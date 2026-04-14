<?php

namespace App\Services;

use App\DTO\FolderDTO;
use App\Exception\{FolderNotFoundException, PersistenceException};
use App\Models\Folder;
use App\Repositories\Interfaces\{DocumentRepositoryInterface, FilesRepositoryInterface, FolderRepositoryInterface};
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

    // --- SECTION : NAVIGATION & CONTENU ---

    /**
     * @inheritDoc
     */
    public function getFolderContents(int $folderId, ?string $searchQuery, bool $archived, ?bool $searchInContent): array
    {
        if ($searchQuery && trim($searchQuery) !== '') {
            return $this->performSearch($folderId, $searchQuery, $archived, $searchInContent);
        }

        return $this->getChildren($folderId, $archived);
    }

    /**
     * @inheritDoc
     */
    public function getRacineChildren(): array
    {
        try {
            $folders = $this->folderRepository->getRacineChildren();
            // Utilisation du mapping récursif pour l'arborescence
            return $folders->map(fn($folder) => $this->mapDTOService->mapToFolderDTO($folder, true))->toArray();
        } catch (Throwable $e) {
            Log::error("Erreur lors de la récupération de la racine", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * @inheritDoc
     */
    public function getBreadcrumbs(int $id): array
    {
        $current = $this->folderRepository->getFolderWithParents($id);
        return $this->generateBreadcrumbs($current);
    }

    /**
     * @inheritDoc
     */
    public function getParentId(int $folder_id): int
    {
        return $this->folderRepository->read($folder_id)->parent_id;
    }

    // --- SECTION : LECTURE & DROITS ---

    /**
     * @inheritDoc
     */
    public function read(int $id): FolderDTO
    {
        try {
            $folder = $this->folderRepository->read($id);
            return $this->mapDTOService->mapToFolderDTO($folder);
        } catch (FolderNotFoundException $e) {
            Log::warning("Dossier introuvable lors de la lecture", ["id" => $id]);
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
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

    // --- SECTION : GESTION (CRUD) ---

    /**
     * @inheritDoc
     */
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

    /**
     * @inheritDoc
     */
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

    /**
     * @inheritDoc
     */
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

    /**
     * @inheritDoc
     */
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

    // --- SECTION : LOGIQUE PRIVÉE ---

    /**
     * Récupère les éléments enfants d'un dossier spécifique.
     */
    private function getChildren(int $id, bool $archived): array
    {
        $folder = $this->folderRepository->getFolderWithContents($id, $archived);

        return [
            'items' => $this->mapDTOService->mapFolderContents($folder),
            'breadcrumbs' => $this->generateBreadcrumbs($folder)
        ];
    }

    /**
     * Effectue une recherche récursive dans les sous-dossiers.
     */
    private function performSearch(int $rootFolderId, string $query, bool $archived, ?bool $searchInContent): array
    {
        $rootFolder = $this->folderRepository->getFolderWithParents($rootFolderId);
        $folderIds = $this->folderRepository->getDescendantFolderIds($rootFolderId);

        $files = $this->filesRepository->performSearch($query, $folderIds, $archived);
        $documents = $this->documentRepository->performSearch($query, $folderIds, $archived, $searchInContent);

        return [
            'items' => $this->mapDTOService->mapFilesAndDocuments($files, $documents),
            'breadcrumbs' => $this->generateBreadcrumbs($rootFolder)
        ];
    }

    /**
     * Construit itérativement le fil d'Ariane vers le haut.
     */
    private function generateBreadcrumbs(Folder $folder): array
    {
        $breadcrumbs = [];
        $temp = $folder;
        while ($temp) {
            array_unshift($breadcrumbs, $this->mapDTOService->mapToFolderDTO($temp));
            $temp = $temp->parent;
        }
        return $breadcrumbs;
    }
}
