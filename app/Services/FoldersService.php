<?php

namespace App\Services;

use App\Repositories\Interfaces\DocumentRepositoryInterface;
use App\Repositories\Interfaces\FilesRepositoryInterface;
use App\DTO\FolderDTO;
use App\Exception\{FolderNotFoundException, PersistenceException};
use App\Models\Folder;
use App\Repositories\Interfaces\FolderRepositoryInterface;
use App\Services\Interfaces\{FoldersServiceInterface, MapDTOServiceInterface, UserServiceInterface};
use Illuminate\Support\Facades\{DB, Log};
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Throwable;

readonly class FoldersService implements FoldersServiceInterface
{

    public function __construct(
        private FolderRepositoryInterface $folderRepository,
        private UserServiceInterface $userService,
        private MapDTOServiceInterface $mapDTOService,
        private FilesRepositoryInterface $filesRepository,
        private DocumentRepositoryInterface $documentRepository,
    ) {}

    // -------------------------------------------------------------------------
    // SECTION : NAVIGATION & CONTENU
    // -------------------------------------------------------------------------

    /**
     * Point d'entrée principal pour l'affichage d'un dossier (Normal ou Recherche)
     */
    public function getFolderContents(int $folderId, ?string $searchQuery, bool $archived, ?bool $searchInContent): array
    {
        if ($searchQuery && trim($searchQuery) !== '') {
            return $this->performSearch($folderId, $searchQuery, $archived, $searchInContent);
        }

        return $this->getChildren($folderId, $archived);
    }

    /**
     * Récupère l'arborescence complète pour le SidebarWidget
     */
    public function getRacineChildren(): array
    {
        try {
            $folders = $this->folderRepository->getRacineChildren();
            return $folders->map(fn($folder) => $this->mapDTOService->mapToFolderDTO($folder, true))->toArray();
        } catch (Throwable $e) {
            Log::error("Erreur lors de la récupération de la racine", ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function getBreadcrumbs(int $id): array
    {
        $current = $this->folderRepository->getFolderWithParents($id);
        return $this->generateBreadcrumbs($current);
    }

    public function getParentId(int $folder_id): int
    {
        return $this->folderRepository->read($folder_id)->parent_id;
    }

    // -------------------------------------------------------------------------
    // SECTION : GESTION (CRUD)
    // -------------------------------------------------------------------------

    public function read(int $id): FolderDTO
    {
        try {
            return $this->mapDTOService->mapToFolderDTO($this->folderRepository->read($id));
        } catch (FolderNotFoundException $e) {
            Log::warning("Dossier introuvable lors de la lecture", ["id" => $id]);
            throw $e;
        }
    }

    public function create(array $data): FolderDTO
    {
        if (empty($data['name']) || empty($data['color'])) {
            throw new BadRequestException("Nom et couleur du dossier requis.");
        }

        try {
            DB::beginTransaction();
            $data["user_id"] = $data["user_id"] ?? $this->userService->getCurrentUserId();
            $folder = $this->folderRepository->create($data);
            DB::commit();

            return $this->mapDTOService->mapToFolderDTO($folder);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Échec de création du dossier", ["data" => $data, "error" => $e->getMessage()]);
            throw new PersistenceException("Impossible de créer le dossier.");
        }
    }

    public function update(int $id, array $data): FolderDTO
    {
        if (empty($id)) throw new BadRequestException("ID manquant.");

        try {
            DB::beginTransaction();
            $folder = $this->folderRepository->update($id, $data);
            DB::commit();

            return $this->mapDTOService->mapToFolderDTO($folder);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Échec de mise à jour du dossier", ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function delete(int $id): void
    {
        try {
            DB::beginTransaction();
            $this->folderRepository->delete($id);
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Erreur lors de la suppression du dossier", ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function restore(int $folder_id): void
    {
        try {
            DB::beginTransaction();
            $this->folderRepository->restore($folder_id);
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Erreur lors de la restauration du dossier", ['id' => $folder_id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function hasEditAccess(int $folder_id): bool
    {
        $user = $this->userService->readById($this->userService->getCurrentUserId());

        if ($user->role === "admin" || empty($folder_id)) {
            return true;
        }

        $folder = $this->read($folder_id);

        if (count($folder->departements) === 0) {
            return true;
        }

        return (bool) array_intersect($user->departements, $folder->departements);
    }

    // -------------------------------------------------------------------------
    // SECTION : LOGIQUE INTERNE (PRIVATE)
    // -------------------------------------------------------------------------

    private function getChildren(int $id, bool $archived): array
    {
        $folder = $this->folderRepository->getFolderWithContents($id, $archived);

        return [
            'items' => collect($this->mapDTOService->mapFolderContents($folder))->sortBy('name')->values(),
            'breadcrumbs' => $this->generateBreadcrumbs($folder)
        ];
    }

    private function performSearch(int $rootFolderId, string $query, bool $archived, ?bool $searchInContent): array
    {
        // Récupération des dossiers parents pour le fil d'Arianne
        $rootFolder = $this->folderRepository->getFolderWithParents($rootFolderId);

        // Récupération de tous les dossiers enfants (enfants des enfants etc...)
        $folderIds = $this->folderRepository->getDescendantFolderIds($rootFolderId);

        // Recherche Fichiers
        $files = $this->filesRepository->performSearch($query, $folderIds, $archived);

        // Recherche Documents
        $documents = $this->documentRepository->performSearch($query, $folderIds, $archived, $searchInContent);

        return [
            'items' => $this->mapDTOService->mapFilesAndDocuments($files, $documents),
            'breadcrumbs' => $this->generateBreadcrumbs($rootFolder)
        ];
    }

    private function generateBreadcrumbs(Folder $folder): array
    {
        $breadcrumbs = [];
        while ($folder) {
            array_unshift($breadcrumbs, $this->mapDTOService->mapToFolderDTO($folder));
            $folder = $folder->parent;
        }
        return $breadcrumbs;
    }
}
