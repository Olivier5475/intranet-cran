<?php

namespace App\Repositories;

use App\Exception\FolderNotFoundException;
use App\Exception\PersistenceException;
use App\Models\Folder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class FolderRepository implements Interfaces\FolderRepositoryInterface
{
    /**
     * Utilise une expression de table commune (CTE) pour récupérer l'arborescence.
     */
    public function getDescendantFolderIds(int $rootFolderId): array
    {
        $query = <<<SQL
            WITH RECURSIVE all_folders (id) AS (
              SELECT id FROM folders WHERE id = ? AND (isDelete = 0 OR isDelete IS NULL)
              UNION ALL
              SELECT f.id FROM folders f
              INNER JOIN all_folders af ON f.parent_id = af.id
              WHERE (f.isDelete = 0 OR f.isDelete IS NULL)
            )
            SELECT id FROM all_folders;
        SQL;

        $results = DB::select($query, [$rootFolderId]);
        return collect($results)->pluck('id')->all();
    }

    public function read(int $id): Folder
    {
        $folder = Folder::where(fn($q) => $q->where('isDelete', false)->orWhereNull('isDelete'))
            ->with([
                'departements:id',
                'children' => fn($q) => $q->where('isDelete', false)->with('departements:id'),
                'files.departements:id',
                'documents.departements:id'
            ])->find($id);

        if (!$folder) {
            throw new FolderNotFoundException("Dossier ID $id introuvable ou supprimé.");
        }

        return $folder;
    }

    public function create(array $data): Folder
    {
        try {
            $folder = new Folder();
            $folder->name = e($data['name']);
            $folder->parent_id = $data['parent_id'] ?? null;
            $folder->user_id = $data['user_id'];
            $folder->color = $data['color'] ?? '#f5be51';
            $folder->isDelete = false;
            $folder->save();

            if (!empty($data['departements'])) {
                $folder->departements()->attach($data['departements']);
            }

            return $folder->load('departements');
        } catch (Throwable $e) {
            Log::error('Erreur SQL : Création de dossier échouée', [
                'message' => $e->getMessage(),
                'data' => $data
            ]);
            throw new PersistenceException("Impossible de créer le dossier.", 0, $e);
        }
    }

    public function update(int $id, array $data): Folder
    {
        $folder = $this->read($id);

        try {
            if (isset($data['name'])) $folder->name = e($data['name']);
            if (isset($data['parent_id'])) $folder->parent_id = $data['parent_id'];
            if (isset($data['color'])) $folder->color = $data['color'];

            $folder->save();

            if (isset($data['departements'])) {
                $folder->departements()->sync($data['departements']);
            }
            return $folder->fresh('departements');
        } catch (Throwable $e) {
            Log::error("Erreur SQL : Mise à jour du dossier $id échouée", [
                'message' => $e->getMessage(),
                'payload' => $data
            ]);
            throw new PersistenceException("Erreur lors de la modification du dossier.", 0, $e);
        }
    }

    public function delete(int $id): void
    {
        $folder = Folder::find($id);

        if (!$folder) {
            throw new FolderNotFoundException("Dossier ID $id introuvable.");
        }

        try {
            // Soft delete manuel via ta colonne isDelete
            $folder->update(['isDelete' => true]);
            Log::info("Dossier marqué comme supprimé", ['id' => $id]);
        } catch (Throwable $e) {
            Log::error("Erreur SQL : Suppression logique du dossier $id échouée", [
                'message' => $e->getMessage()
            ]);
            throw new PersistenceException("Erreur technique lors de la suppression.", 0, $e);
        }
    }

    public function getRacineChildren(): Collection
    {
        return Folder::whereNull('parent_id')
            ->where(fn($q) => $q->where('isDelete', false)->orWhereNull('isDelete'))
            ->with(['allChildren' => fn($q) => $q->where('isDelete', false)])
            ->get();
    }

    public function getFolderWithContents(int $id): Folder
    {
        return Folder::where(fn($q) => $q->where('isDelete', false)->orWhereNull('isDelete'))
            ->with([
                'departements:id',
                'children' => fn($q) => $q->where('isDelete', false)->with('departements:id'),
                'files.departements:id',
                'documents.departements:id'
            ])
            ->findOrFail($id);
    }

    public function getFolderWithParents(int $id): Folder
    {
        return Folder::where(fn($q) => $q->where('isDelete', false)->orWhereNull('isDelete'))
            ->with('parent.parent.parent.parent.parent')
            ->findOrFail($id);
    }
}
