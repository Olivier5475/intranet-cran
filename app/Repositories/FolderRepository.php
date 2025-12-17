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
        // C'est notre fameuse CTE Récursive en SQL pur
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
        $folder = Folder::find($id);

        if(!$folder){
            throw new FolderNotFoundException("Folder with ID ".$id." not found");
        }

        return $folder;
    }


    public function create(array $data): Folder {
        try {
            return Folder::create($data);
        } catch (\Throwable $e) {
            Log::error('Folder creation error', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            throw new PersistenceException(message:"Could not create Folder.", previous:$e);
        }
    }

    public function update(int $id, array $data): Folder|bool {
        $folder = Folder::find($id);

        if (!$folder) {
            throw new FolderNotFoundException("Folder with ID $id not found.");
        }

        try {
            $folder->fill($data);
            $result = $folder->save();

            return $result ? $folder : false;
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
        try {
            return Folder::where('parent_id', '=', null)
                ->with('allChildren') // Charge tous les enfants
                ->get();
        }
        catch (\Throwable $e) {
            Log::error('Folder getRacineChildren error', ["message" => $e->getMessage()]);
            throw new FolderNotFoundException();
        }
    }
}
