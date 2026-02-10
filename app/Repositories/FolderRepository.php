<?php

namespace App\Repositories;

use App\Exception\FolderNotFoundException;
use App\Exception\PersistenceException;
use App\Models\Folder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FolderRepository implements Interfaces\FolderRepositoryInterface
{
    public function getDescendantFolderIds(int $rootFolderId): array
    {
        // Ajout de la condition isDelete dans la récursivité
        $query = <<<SQL
            WITH RECURSIVE all_folders (id) AS (
              SELECT id FROM folders WHERE id = ? AND (isDelete IS NULL OR isDelete = 0)

              UNION ALL

              SELECT f.id FROM folders f
              INNER JOIN all_folders af ON f.parent_id = af.id
              WHERE (f.isDelete IS NULL OR f.isDelete = 0)
            )
            SELECT id FROM all_folders;
        SQL;

        $results = DB::select($query, [$rootFolderId]);
        return collect($results)->pluck('id')->all();
    }

    public function read(int $id): Folder
    {
        $folder = Folder::where('isDelete', false)->orWhereNull('isDelete')
        ->with([
            'departements:id',
            'children' => fn($q) => $q->where('isDelete', false)->with('departements:id'),
            'files.departements:id',
            'documents.departements:id'
        ])->find($id);

        if (!$folder) {
            throw new FolderNotFoundException("Folder with ID " . $id . " not found or deleted");
        }

        return $folder;
    }

    public function create(array $data): Folder
    {
        try {
            $folder = new Folder();
            $folder->name = e($data['name']);
            $folder->parent_id = $data['parent_id'];
            $folder->user_id = $data['user_id'];
            $folder->color = $data['color'];
            $folder->isDelete = false; // Initialisation explicite
            $folder->save();
            $folder->departements()->attach($data['departements']);
            return $folder;
        } catch (\Throwable $e) {
            Log::error('Folder creation error', ['error' => $e->getMessage()]);
            throw new PersistenceException(message: "Could not create Folder.", previous: $e);
        }
    }

    public function update(int $id, array $data): Folder
    {
        $folder = Folder::where('isDelete', false)->orWhereNull('isDelete')->find($id);

        if (!$folder) {
            throw new FolderNotFoundException("Folder with ID $id not found or deleted.");
        }

        try {
            if (isset($data['name'])) $folder->name = e($data['name']);
            if (isset($data['parent_id'])) $folder->parent_id = $data['parent_id'];
            if (isset($data['color'])) $folder->color = $data['color'];
            if (isset($data['user_id'])) $folder->user_id = $data['user_id'];

            $folder->save();

            if (isset($data['departements'])) {
                $folder->departements()->sync($data['departements']);
            }
            return $folder;
        } catch (\Throwable $e) {
            Log::error('Folder update failed', ['error' => $e->getMessage()]);
            throw new PersistenceException(message: "Could not update folder.", previous: $e);
        }
    }

    public function delete(int $id): void
    {
        $folder = Folder::find($id);

        if (!$folder) {
            throw new FolderNotFoundException("Folder with ID $id not found.");
        }

        try {
            $folder->isDelete = true;
            $folder->save();
        } catch (\Throwable $e) {
            Log::error('Folder delete failed', ['error' => $e->getMessage()]);
            throw new PersistenceException(message: "Could not delete folder.", previous: $e);
        }
    }

    public function getRacineChildren(): Collection
    {
        return Folder::whereNull('parent_id')->orWhere("isDelete", false)->where('isDelete', null)
            ->with(['allChildren' => fn($q) => $q->where('isDelete', false)->orWhereNull('isDelete')])
            ->get();
    }

    public function getFolderWithContents(int $id): Folder
    {
        return Folder::where('isDelete', false)->orWhereNull('isDelete')
            ->with([
                'departements:id',
                'children' => fn($q) => $q->where('isDelete', false)->orWhereNull('isDelete')->with('departements:id'),
                'files.departements:id',
                'documents.departements:id'
            ])
            ->findOrFail($id);
    }

    public function getFolderWithParents(int $id): Folder
    {
        // Note : pour les parents récursifs, il faudrait idéalement
        // filtrer isDelete sur chaque niveau de la relation dans le modèle
        return Folder::where('isDelete', false)->orWhereNull('isDelete')
            ->with('parent.parent.parent.parent.parent')
            ->findOrFail($id);
    }
}
