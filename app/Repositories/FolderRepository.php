<?php

namespace App\Repositories;

use App\Exception\FolderNotFoundException;
use App\Exception\PersistenceException;
use App\Models\Folder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FolderRepository implements Interfaces\FolderRepositoryInterface{

    public function getDescendantFolderIds(int $rootFolderId): array {
        $query = <<<SQL
            WITH RECURSIVE all_folders (id) AS (
              SELECT id FROM folders WHERE id = ?

              UNION ALL

              SELECT f.id FROM folders f
              INNER JOIN all_folders af ON f.parent_id = af.id
            )
            SELECT id FROM all_folders;
        SQL;

        // On exécute la requête en passant l'ID de départ
        $results = DB::select($query, [$rootFolderId]);

        // On transforme le résultat (array d'objets) en un simple array d'IDs
        return collect($results)->pluck('id')->all();
    }

    public function read(int $id): Folder {
        $folder = Folder::with([
            'departements:id',
            'children.departements:id', // Charge les départements des sous-dossiers
            'files.departements:id',    // Charge les départements des fichiers
            'documents.departements:id' // Charge les départements des documents
        ])->find($id);

        if(!$folder){
            throw new FolderNotFoundException("Folder with ID ".$id." not found");
        }

        return $folder;
    }


    public function create(array $data): Folder {
        try {
            $folder = new Folder();
            $folder->name = e($data['name']);
            $folder->parent_id = $data['parent_id'];
            $folder->user_id = $data['user_id'];
            $folder->color = $data['color'];
            $folder->save();
            $folder->departements()->attach($data['departements']);
            return $folder;
        } catch (\Throwable $e) {
            Log::error('Folder creation error', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            throw new PersistenceException(message:"Could not create Folder.", previous:$e);
        }
    }

    public function update(int $id, array $data): Folder {
        $folder = Folder::with("departements:id")->find($id);

        if (!$folder) {
            throw new FolderNotFoundException("Folder with ID $id not found.");
        }

        try {
            if(isset($data['name'])){
                $folder->name = e($data['name']);
            }
            if(isset($data['parent_id'])){
                $folder->parent_id = $data['parent_id'];
            }
            if(isset($data['color'])){
                $folder->color = $data['color'];
            }
            if(isset($data['user_id'])){
                $folder->user_id = $data['user_id'];
            }
            $folder->save();
            if(isset($data['departements'])){
                $folder->departements()->sync($data['departements']);
            }
            return $folder;
        } catch (\Throwable $e) {
            Log::error('Folder update failed for ID ' . $id, [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            throw new PersistenceException(message : "Could not update document with ID $id.", previous:$e);
        }
    }

    public function delete(int $id): bool {
        $folder = Folder::find($id);

        if (!$folder) {
            throw new FolderNotFoundException("Folder with ID $id not found.");
        }

        try {
            $folder->delete();
            return true;
        } catch (\Throwable $e) {
            Log::error('Folder delete failed for ID ' . $id, [
                'error' => $e->getMessage(),
            ]);

            throw new PersistenceException(message : "Could not delete folder with ID $id.", previous:$e);
        }
    }

    public function getRacineChildren(): Collection {
        return Folder::where('parent_id', '=', null)
            ->with('allChildren')
            ->get();
    }

    public function getFolderWithContents(int $id): Folder
    {
        return Folder::with([
            'departements:id',
            'children.departements:id',
            'files.departements:id',
            'documents.departements:id'
        ])->findOrFail($id);
    }

    public function getFolderWithParents(int $id): Folder {
        return Folder::with('parent.parent.parent.parent.parent')->findOrFail($id);
    }
}
