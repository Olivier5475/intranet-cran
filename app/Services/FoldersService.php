<?php

namespace App\Services;

use App\DTO\DepartementDTO;
use App\DTO\DocumentDTO;
use App\DTO\FileDTO;
use App\DTO\FolderDTO;
use App\Models\Document;
use App\Models\File;
use App\Models\Folder;
use App\repositories\interfaces\FolderRepositoryInterface;
use App\Services\Interface\FoldersServiceInterface;
use Illuminate\Support\Collection;

class FoldersService implements FoldersServiceInterface {
    public function __construct(
        private FolderRepositoryInterface $folderRepository,
    ) {}

    public function getChildren(int $id): array {
        $current = Folder::find($id);
        $children = $current->children;
        $files = $current->files;
        $documents = $current->documents;

        $res = [];
        foreach ($children as $child) {
            $res[] = new FolderDTO(
                id:$child->id,
                name:$child->name,
                created_at:$child->created_at,
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
                id:$file->id,
                name:$file->name,
                type: "file",
                departements:$departementsDTOs,
                created_at:$file->created_at,
            );
        }
        foreach ($documents as $document) {
            $res[] = new DocumentDTO(
                id: $document->id,
                name: $document->name,
                type: "document",
                departements: $document->departements,
                created_at: $document->created_at,
            );
        }
        return $res;
    }

    public function getBreadcrumbs(int $id): array {
        $breadcrumbs = [];

        $current = Folder::find($id);
        while ($current) {
            array_unshift($breadcrumbs, new FolderDTO(
                id: $current->id,
                name: $current->name,
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
                    id:$child->id,
                    name:$child->name
                );
            }
            $res[] = new FolderDTO(
                id:$folder->id,
                name:$folder->name,
                children: $children
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
            id : $f->id,
            name : $f->name,
            created_at: $f->created_at,
        ));

        $fileDTOs = $files->map(fn($f) => new FileDTO(
            id : $f->id,
            name : $f->name,
            type : "file",
            departements: $this->getDepartementsDTOs($f->departements),
            created_at: $f->created_at,
        ));
        $documentDTOs = $documents->map(fn($f) => new DocumentDTO(
            id : $f->id,
            name : $f->title,
            type : "file",
            departements: $this->getDepartementsDTOs($f->departements),
            created_at: $f->created_at,
        ));

        return collect([])->merge($folderDTOs)->merge($fileDTOs)->merge($documentDTOs)
            ->sortBy('name')->values();
    }

    /*
     * Méthode privée pour la recherche
     */
    private function performSearch(int $rootFolderId, string $query) : Collection {
        // 1. On utilise le Repository pour avoir les IDs
        $folderIds = $this->folderRepository->getDescendantFolderIds($rootFolderId);

        // 2. On cherche dans Meilisearch (Scout)
        $files = File::search($query)->whereIn('folder_id', $folderIds)->get();
        $documents = Document::search($query)->whereIn('folder_id', $folderIds)->get();

        // 3. On transforme en DTOs
        $fileDTOs = $files->map(fn($f) => new FileDTO(
            id : $f->id,
            name : $f->name,
            type : "file",
            departements: $this->getDepartementsDTOs($f->departements),
            created_at: $f->created_at,
        ));
        $documentDTOs = $documents->map(fn($f) => new DocumentDTO(
            id : $f->id,
            name : $f->title,
            type : "file",
            departements: $this->getDepartementsDTOs($f->departements),
            created_at: $f->created_at,
        ));

        // Pas de tri par nom, Meilisearch trie par pertinence !
        return collect([])->merge($fileDTOs)->merge($documentDTOs);
    }

    private function getDepartementsDTOs(Collection $departements): array {
        $departementsDTOs = [];
        foreach ($departements as $departement) {
            $departementsDTOs[] = new DepartementDTO(
                id: $departement->id,
                name: $departement->name,
                initials: $departement->initials
            );
        };
        return $departementsDTOs;
    }
}
