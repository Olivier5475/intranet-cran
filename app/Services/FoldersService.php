<?php

namespace App\Services;

use App\DTO\DocumentDTO;
use App\DTO\FileDTO;
use App\DTO\FolderDTO;
use App\Exception\FolderNotFoundException;
use App\Exception\PersistenceException;
use App\Models\Document;
use App\Models\File;
use App\Models\Folder;
use App\Repositories\Interfaces\FolderRepositoryInterface;
use App\Services\Interfaces\UserServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Throwable;

readonly class FoldersService implements Interfaces\FoldersServiceInterface {

    public function __construct(
        private FolderRepositoryInterface $folderRepository,
        private UserServiceInterface $userService,
    ) {}

    public function getChildren(int $id): array
    {
        // Utilisation du repository pour charger les relations proprement
        $current = $this->folderRepository->getFolderWithContents($id);

        $res = [];
        foreach ($current->children as $child) {
            $res[] = $this->mapToFolderDTO($child);
        }
        foreach ($current->files as $file) {
            $res[] = new FileDTO(
                id: $file->id,
                name: $file->name,
                departements: $file->departements->pluck('id')->toArray(),
                created_at: $file->created_at,
                folder_id: $file->folder_id,
                storage_path: $file->storage_path,
                mimetype: $file->mimetype,
            );
        }
        foreach ($current->documents as $document) {
            $res[] = new DocumentDTO(
                id: $document->id,
                name: $document->title,
                departements: $document->departements->pluck('id')->toArray(),
                created_at: $document->created_at,
                color: $document->color,
            );
        }
        return $res;
    }

    public function getBreadcrumbs(int $id): array
    {
        $breadcrumbs = [];
        $current = $this->folderRepository->getFolderWithParents($id);

        while ($current) {
            array_unshift($breadcrumbs, $this->mapToFolderDTO($current));
            $current = $current->parent;
        }

        return $breadcrumbs;
    }

    public function getRacineChildren() : array
    {
        try {
            $folders = $this->folderRepository->getRacineChildren();
            return $folders->map(fn($f) => $this->mapToFolderDTO($f))->toArray();
        } catch (Throwable $e) {
            Log::error("Erreur lors de la récupération de la racine", ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function getFolderContents(int $folderId, ?string $searchQuery) : Collection
    {
        if ($searchQuery && trim($searchQuery) !== '') {
            return $this->performSearch($folderId, $searchQuery);
        }
        return collect($this->getChildren($folderId))->sortBy('name')->values();
    }

    private function performSearch(int $rootFolderId, string $query) : Collection
    {
        $folderIds = $this->folderRepository->getDescendantFolderIds($rootFolderId);

        // Recherche via Scout (Meilisearch)
        $files = File::search($query)->whereIn('folder_id', $folderIds)->get();
        $documents = Document::search($query)->whereIn('folder_id', $folderIds)->get();

        $fileDTOs = $files->map(fn($f) => new FileDTO(
            id: $f->id,
            name: $f->name,
            departements: $f->departements->pluck('id')->toArray(),
            created_at: $f->created_at,
            folder_id: $f->folder_id,
            storage_path: $f->storage_path,
            mimetype: $f->mimetype,
        ));

        $documentDTOs = $documents->map(fn($d) => new DocumentDTO(
            id: $d->id,
            name: $d->title,
            departements: $d->departements->pluck('id')->toArray(),
            created_at: $d->created_at,
            color: $d->color,
        ));

        return $fileDTOs->concat($documentDTOs);
    }

    public function read(int $id): FolderDTO
    {
        try {
            return $this->mapToFolderDTO($this->folderRepository->read($id));
        } catch (FolderNotFoundException $e) {
            Log::warning("Dossier introuvable lors de la lecture", ["id" => $id]);
            throw $e;
        }
    }

    public function create(array $data): FolderDTO
    {
        if(empty($data['name']) || empty($data['color'])) {
            throw new BadRequestException("Nom et couleur du dossier requis.");
        }

        try {
            DB::beginTransaction();
            $data["user_id"] = $this->userService->getCurrentUserId();
            $folder = $this->folderRepository->create($data);
            DB::commit();

            return $this->mapToFolderDTO($folder);

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Échec de création du dossier", [
                "data" => $data,
                "error" => $e->getMessage()
            ]);
            throw new PersistenceException("Impossible de créer le dossier.");
        }
    }

    public function update(int $id, array $data): FolderDTO
    {
        if(empty($id)) throw new BadRequestException("ID manquant.");

        try {
            DB::beginTransaction();
            $folder = $this->folderRepository->update($id, $data);
            DB::commit();

            return $this->mapToFolderDTO($folder);

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
            Log::info("Dossier supprimé avec succès", ['id' => $id]);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Erreur lors de la suppression du dossier", ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function hasEditAccess(int $folder_id): bool
    {
        $user = $this->userService->readById($this->userService->getCurrentUserId());

        if($user->role === "admin" || empty($folder_id)) {
            return true;
        }
        $folder = $this->read($folder_id);

        if(count($folder->departements) === 0) {
            return true;
        }
        return (bool) array_intersect($user->departements, $folder->departements);
    }

    private function mapToFolderDTO(Folder $folder): FolderDTO
    {
        return new FolderDTO(
            id: $folder->id,
            name: $folder->name,
            departements: $folder->relationLoaded('departements') ? $folder->departements->pluck('id')->toArray() : [],
            color: $folder->color,
            created_at: $folder->created_at
        );
    }
}
