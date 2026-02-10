<?php

namespace App\Services;

use App\DTO\DepartementDTO;
use App\DTO\DocumentDTO;
use App\DTO\FileDTO;
use App\DTO\FolderDTO;
use App\Exception\DiskWriteException;
use App\Exception\FolderNotFoundException;
use App\Exception\PersistenceException;
use App\Models\Document;
use App\Models\File;
use App\Models\Folder;
use App\Repositories\Interfaces\DepartementRepositoryInterface;
use App\Repositories\Interfaces\FolderRepositoryInterface;
use App\Services\Interfaces\DepartementsServiceInterface;
use App\Services\Interfaces\UserServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Throwable;

readonly class FoldersService implements Interfaces\FoldersServiceInterface {
    public function __construct(
        private FolderRepositoryInterface $folderRepository,
        private UserServiceInterface $userService,
    ) {}

    public function getChildren(int $id): array
    {
        $current = Folder::find($id);
        $children = $current->children;
        $files = $current->files;
        $documents = $current->documents;

        $res = [];
        foreach ($children as $child) {
            $res[] = new FolderDTO(
                id: $child->id,
                name: $child->name,
                departements: $child->departements->pluck('id')->toArray(),
                color: '#f5be51',
                created_at: $child->created_at,
            );
        }
        foreach ($files as $file) {
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
        foreach ($documents as $document) {
            $res[] = new DocumentDTO(
                id: $document->id,
                name: $document->name,
                departements: $document->departements->pluck('id')->toArray(),
                created_at: $document->created_at,
                color: '#f5be51',
            );
        }
        return $res;
    }

    public function getBreadcrumbs(int $id): array
    {
        $breadcrumbs = [];
        $current = $this->folderRepository->getFolderWithParents($id);

        while ($current) {
            array_unshift($breadcrumbs, new FolderDTO(
                id: $current->id,
                name: $current->name,
                departements: $current->departements->pluck('id')->toArray(),
                color: '#f5be51',
            ));
            $current = $current->parent;
        }

        return $breadcrumbs;
    }

    /**
     * @throws FolderNotFoundException
     */
    public function getRacineChildren() : array
    {
        try {
            $folders = $this->folderRepository->getRacineChildren();
        } catch (FolderNotFoundException $e) {
            Log::warning("Folder not found in getRacineChildren" , []);
            throw $e;
        }

        $res = [];
        foreach ($folders as $folder) {
            $res[] = $this->mapFolderToDTO($folder);
        }
        return $res;
    }

    public function getFolderContents(int $folderId, ?string $searchQuery) : Collection
    {
        if ($searchQuery && $searchQuery !== '') {
            // MODE RECHERCHE
            return $this->performSearch($folderId, $searchQuery);
        }

        // MODE NAVIGATION
        return $this->getRegularContents($folderId);
    }

    /**
     * Convertit un modèle Folder (avec ses relations) en FolderDTO récursivement.
     */
    private function mapFolderToDTO(Folder $folder): FolderDTO
    {

        // Gestion des enfants (inchangée)
        $children = [];
        if ($folder->relationLoaded('allChildren') && $folder->allChildren->isNotEmpty()) {
            foreach ($folder->allChildren as $child) {
                $children[] = $this->mapFolderToDTO($child);
            }
        }

        $departementsIds = $folder->relationLoaded('departements')
            ? $folder->departements->pluck('id')->toArray()
            : [];

        return new FolderDTO(
            id: $folder->id,
            name: $folder->name,
            departements: $departementsIds,
            color: $folder->color,
            children: $children,
        );
    }

    /*
     * Méthode privée pour la navigation
     */
    private function getRegularContents(int $folderId): Collection
    {
        $folder = $this->folderRepository->getFolderWithContents($folderId);
        $folderDTOs = $folder->children->map(fn($f) => new FolderDTO(
            id: $f->id,
            name: $f->name,
            departements: $f->departements->pluck('id')->toArray(),
            color: $f->color,
            created_at: $f->created_at
        ));

        // On transforme les fichiers
        $fileDTOs = $folder->files->map(fn($f) => new FileDTO(
            id: $f->id,
            name: $f->name,
            departements: $f->departements->pluck('id')->toArray(),
            created_at: $f->created_at,
            folder_id: $f->folder_id,
            storage_path: $f->storage_path,
            mimetype: $f->mimetype,
        ));

        // On transforme les documents
        $documentDTOs = $folder->documents->map(fn($d) => new DocumentDTO(
            id: $d->id,
            name: $d->title,
            departements: $d->departements->pluck('id')->toArray(),
            created_at: $d->created_at,
            color: $d->color,
        ));

        return $folderDTOs->concat($fileDTOs)->concat($documentDTOs)
            ->sortBy('name')->values();
    }

    /*
     * Méthode privée pour la recherche
     */
    private function performSearch(int $rootFolderId, string $query) : Collection
    {
        // 1. On utilise le Repository pour avoir les IDs
        $folderIds = $this->folderRepository->getDescendantFolderIds($rootFolderId);

        // 2. On cherche dans Meili search (Scout)
        $files = File::search($query)->whereIn('folder_id', $folderIds)->get();
        $documents = Document::search($query)->whereIn('folder_id', $folderIds)->get();

        // 3. On transforme en DTOs
        $fileDTOs = $files->map(fn($f) => new FileDTO(
            id: $f->id,
            name: $f->name,
            departements: $f->departements->pluck('id')->toArray(),
            created_at: $f->created_at,
            folder_id: $f->folder_id,
            storage_path: $f->storage_path,
            mimetype: $f->mimetype,
        ));
        $documentDTOs = $documents->map(fn($f) => new DocumentDTO(
            id: $f->id,
            name: $f->title,
            departements: $f->departements->pluck('id')->toArray(),
            created_at: $f->created_at,
            color: $f->color,
        ));

        // Pas de tri par nom, Meili search trie par pertinence !
        return collect()->merge($fileDTOs)->merge($documentDTOs);
    }

    public function read(int $id): FolderDTO
    {
        try {
            $folder = $this->folderRepository->read($id);
            return new FolderDTO(
                id: $folder->id,
                name: $folder->name,
                departements: $folder->departements->pluck('id')->toArray(),
                color: $folder->color,
            );
        } catch (FolderNotFoundException $e) {
            Log::warning("Document was not foud", [
                "id" => $id,
            ]);
            throw $e;
        }
    }

    public function create(array $data): FolderDTO
    {
        if(empty($data['name']) || empty($data['color'])) {
            throw new BadRequestException("Missing argument(s)");
        }

        try {
            $data["user_id"] = $this->userService->getCurrentUserId();

            DB::beginTransaction();

            $folder = $this->folderRepository->create($data);

            DB::commit(); // Tout a réussi
            return new FolderDTO(
                id: $folder->id,
                name: $folder->name,
                departements: $folder->departements->pluck('id')->toArray(),
                color: $folder->color,
            );
        } catch (PersistenceException $e) {
            Log::warning('Document attempted to create can\'t be persisted.', [
                "message" => $e->getMessage()
            ]);

            try {
                DB::rollBack();
            } catch (Throwable) {
                Log::error("Fatal error during rollback", []);
            }

            throw $e;
        } catch (DiskWriteException|FolderNotFoundException $e) {
            Log::warning('Attachment attempted to create can\'t be persisted.');

            try {
                DB::rollBack();
            } catch (Throwable) {
                Log::error("Fatal error during rollback", []);
            }

            throw $e;
        } catch (Throwable $e) {

            throw new InternalErrorException("UNEXPECTED FATAL ERROR. ".$e->getMessage());
        }
    }

    public function update(int $id, array $data): FolderDTO|bool
    {
        if(empty($id)) {
            throw new BadRequestException("Missing argument");
        }

        try {
            DB::beginTransaction();
            $folder = $this->folderRepository->update($id, $data);
            DB::commit(); // Rien n'a changé, on valide
            return new FolderDTO(
                id: $folder->id,
                name: $folder->name,
                departements: $folder->departements->pluck('id')->toArray(),
                color: $folder->color,
            );
        } catch (Throwable $e) { // Attrape toutes les erreurs BD ou Disque
            Log::warning("Transaction failed during folder/attachment update.", [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            try {
                DB::rollBack();
            } catch (Throwable) {
                Log::error("Fatal error during rollback", []);
            }
            throw $e;
        }
    }

    public function delete(int $id): void
    {
        try {
            $this->folderRepository->delete($id);
        } catch (FolderNotFoundException $e) {
            Log::warning('Document attempted to delete was not found.', ['id' => $id]);
            throw $e;
        } catch (PersistenceException $e) {
            Log::warning('Document with ID'.$id.'can\'t be deleted', ['id' => $id]);
            throw $e;
        }
    }

    public function hasEditAccess(int $folder_id): bool
    {
        $user = $this->userService->readById($this->userService->getCurrentUserId());
        $folder = $this->read($folder_id);
        if($user->role === "admin" || empty($folder->parent_id) || $folder->departements === []) {
            return true;
        }
        return (bool) array_intersect($user->departements, $folder->departements);
    }
}
