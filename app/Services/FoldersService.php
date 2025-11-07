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
use App\Repositories\Interfaces\FolderRepositoryInterface;
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

    public function getChildren(int $id): array {
        $current = Folder::find($id);
        $children = $current->children;
        $files = $current->files;
        $documents = $current->documents;

        $res = [];
        foreach ($children as $child) {
            $res[] = new FolderDTO(
                id: $child->id,
                name: $child->name,
                color: '#f5be51',
                created_at: $child->created_at,
            );
        }
        foreach ($files as $file) {
            $departements = $file->departements;
            $departementsDTOs = [];
            foreach ($departements as $departement) {
                $departementsDTOs[] = new DepartementDTO(
                    id: $departement->id,
                    name: $departement->name,
                    initials: $departement->initials
                );
            }
            $res[] = new FileDTO(
                id: $file->id,
                name: $file->name,
                departements: $departementsDTOs,
                created_at: $file->created_at,
                storage_path: $file->storage_path,
                mimetype: $file->mimetype,
            );
        }
        foreach ($documents as $document) {
            $res[] = new DocumentDTO(
                id: $document->id,
                name: $document->name,
                departements: $document->departements,
                created_at: $document->created_at,
                color: '#f5be51',
            );
        }
        return $res;
    }

    public function getBreadcrumbs(int $id): array {
        if(empty($id)) {
            throw new BadRequestException("Missing argument", 400);
        }

        $breadcrumbs = [];

        try {
            $current = $this->folderRepository->read($id);
        } catch (FolderNotFoundException $e) {
            Log::warning("Folder not found" , [
                "id" => $id,
                "message" => $e->getMessage()
            ]);
            throw $e;
        }
        while ($current) {
            array_unshift($breadcrumbs, new FolderDTO(
                id: $current->id,
                name: $current->name,
                color: '#f5be51',
            ));
            $current = $current->parent;
        }

        return $breadcrumbs;
    }

    public function getRacineChildren() : array {
        $folders = Folder::where('parent_id', '=', null)->get();
        $res = [];
        foreach ($folders as $folder) {
            $children = [];
            foreach($folder->children as $child) {
                $children[] = new FolderDTO(
                    id: $child->id,
                    name: $child->name,
                    color: $child->color,
                );
            }
            $res[] = new FolderDTO(
                id: $folder->id,
                name: $folder->name,
                color: $folder->color,
                children: $children,
            );
        }
        return $res;
    }

    public function getFolderContents(int $folderId, ?string $searchQuery) : Collection {
        if ($searchQuery && $searchQuery !== '') {
            // MODE RECHERCHE
            return $this->performSearch($folderId, $searchQuery);
        }

        // MODE NAVIGATION
        return $this->getRegularContents($folderId);
    }

    /*
     * Méthode privée pour la navigation
     */
    private function getRegularContents(int $folderId): Collection {
        $folder = Folder::findOrFail($folderId);

        $subfolders = $folder->children()->get();
        $files = $folder->files()->get();
        $documents = $folder->documents()->get();

        $folderDTOs = $subfolders->map(fn($f) => new FolderDTO(
            id: $f->id,
            name: $f->name,
            color: $f->color,
            created_at: $f->created_at
        ));

        $fileDTOs = $files->map(fn($f) => new FileDTO(
            id: $f->id,
            name: $f->name,
            departements: $this->getDepartementsDTOs($f->departements),
            created_at: $f->created_at,
            storage_path: $f->storage_path,
            mimetype: $f->mimetype,
        ));
        $documentDTOs = $documents->map(fn($f) => new DocumentDTO(
            id: $f->id,
            name: $f->title,
            departements: $this->getDepartementsDTOs($f->departements),
            created_at: $f->created_at,
            color: $f->color,
        ));

        return collect()->merge($folderDTOs)->merge($fileDTOs)->merge($documentDTOs)
            ->sortBy('name')->values();
    }

    /*
     * Méthode privée pour la recherche
     */
    private function performSearch(int $rootFolderId, string $query) : Collection {
        // 1. On utilise le Repository pour avoir les IDs
        $folderIds = $this->folderRepository->getDescendantFolderIds($rootFolderId);

        // 2. On cherche dans Meili search (Scout)
        $files = File::search($query)->whereIn('folder_id', $folderIds)->get();
        $documents = Document::search($query)->whereIn('folder_id', $folderIds)->get();

        // 3. On transforme en DTOs
        $fileDTOs = $files->map(fn($f) => new FileDTO(
            id: $f->id,
            name: $f->name,
            departements: $this->getDepartementsDTOs($f->departements),
            created_at: $f->created_at,
            storage_path: $f->storage_path,
            mimetype: $f->mimetype,
        ));
        $documentDTOs = $documents->map(fn($f) => new DocumentDTO(
            id: $f->id,
            name: $f->title,
            departements: $this->getDepartementsDTOs($f->departements),
            created_at: $f->created_at,
            color: $f->color,
        ));

        // Pas de tri par nom, Meili search trie par pertinence !
        return collect()->merge($fileDTOs)->merge($documentDTOs);
    }

    private function getDepartementsDTOs(Collection $departements): array {
        $departementsDTOs = [];
        foreach ($departements as $departement) {
            $departementsDTOs[] = new DepartementDTO(
                id: $departement->id,
                name: $departement->name,
                initials: $departement->initials
            );
        }
        return $departementsDTOs;
    }

    public function read(int $id): FolderDTO {
        try {
            $folder = $this->folderRepository->read($id);
            return new FolderDTO(
                id: $folder->id,
                name: $folder->name,
                color: $folder->color,
            );
        } catch (FolderNotFoundException $e) {
            Log::warning("Document was not foud", [
                "id" => $id,
            ]);
            throw $e;
        }
    }

    public function create(array $data): FolderDTO {
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

    public function update(int $id, array $data): FolderDTO|bool {
        if(empty($id)) {
            throw new BadRequestException("Missing argument");
        }

        try {
            DB::beginTransaction();
            $folder = $this->folderRepository->update($id, $data);

            if ($folder instanceof Folder) {
                DB::commit(); // Tout a réussi
                return new FolderDTO(
                    id: $folder->id,
                    name: $folder->name,
                    color: $folder->color,
                );
            }

            DB::commit(); // Rien n'a changé, on valide
            return $folder;

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

    public function delete(int $id): bool {
        try {
            return $this->folderRepository->delete($id);
        } catch (FolderNotFoundException $e) {
            Log::warning('Document attempted to delete was not found.', ['id' => $id]);
            throw $e;
        } catch (PersistenceException $e) {
            Log::warning('Document with ID'.$id.'can\'t be deleted', ['id' => $id]);
            throw $e;
        }
    }
}
