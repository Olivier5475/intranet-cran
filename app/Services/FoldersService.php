<?php

namespace App\Services;

use App\DTO\DocumentDTO;
use App\DTO\FileDTO;
use App\DTO\FolderDTO;
use App\Interface\FoldersServiceInterface;
use App\Models\Folder;

class FoldersService implements FoldersServiceInterface {

    public function getParent(int $id): ?FolderDTO {
        $current = Folder::find($id);
        $parent = $current->parent;
        return new FolderDTO(
            id: $parent->id,
            name: $parent->name
        );
    }

    public function getChildren(int $id): array {
        $current = Folder::find($id);
        $children = $current->children;
        $files = $current->files;
        $documents = $current->documents;

        $res = [];
        foreach ($children as $child) {
            $res[] = new FolderDTO(
                id:$child->id,
                name:$child->name
            );
        }
        foreach ($files as $file) {
            $res[] = new FileDTO(
                id:$file->id,
                name:$file->name
            );
        }
        foreach ($documents as $document) {
            $res[] = new DocumentDTO(
                id: $document->id,
                name:$document->title,
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
                name: $current->name
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
}
