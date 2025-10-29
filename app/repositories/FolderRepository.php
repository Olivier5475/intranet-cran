<?php

namespace App\repositories;

use App\repositories\interfaces\FolderRepositoryInterface;
use Illuminate\Support\Facades\DB;

class FolderRepository implements FolderRepositoryInterface{

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
}
