<?php

namespace App\Repositories;

use App\Exception\{AlreadyExistsException, FileNotFoundException, PersistenceException};
use App\Models\{Document, File, Version};
use App\Repositories\Interfaces\FilesRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class FilesRepository implements FilesRepositoryInterface
{
    // --- LECTURE ---

    /**
     * @inheritDoc
     */
    public function read(int $id): File
    {
        $file = File::with("departements")->find($id);

        if (!$file) {
            throw new FileNotFoundException("Le fichier avec l'ID $id est introuvable.");
        }
        return $file;
    }

    // --- ÉCRITURE (CRUD) ---

    /**
     * @inheritDoc
     */
    public function create(array $data): File
    {
        if ($this->checkName($data["folder_id"] ?? null, $data["name"])) {
            throw new AlreadyExistsException("Un fichier ou document porte déjà ce nom dans ce dossier.");
        }

        try {
            $file = new File();
            $file->name = e($data['name']);
            $file->folder_id = $data['folder_id'] ?? null;
            $file->user_id = $data['user_id'];
            $file->storage_path = $data['storage_path'];
            $file->mimetype = $data['mimetype'];
            $file->size = $data['size'];
            $file->save();

            if (!empty($data['departements'])) {
                $file->departements()->attach($data['departements']);
            }

            return $file->load('departements');
        } catch (Throwable $e) {
            Log::error('Erreur SQL : Création de fichier échouée', [
                'message' => $e->getMessage(),
                'data' => $data,
            ]);
            throw new PersistenceException("Impossible de créer l'entrée du fichier en base de données.", 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $data): File
    {
        $file = $this->read($id);

        if (isset($data["name"]) && $this->checkName($file->folder_id, $data["name"], $id)) {
            throw new AlreadyExistsException("Le nouveau nom est déjà utilisé.");
        }

        try {
            if (isset($data["name"])) $file->name = e($data["name"]);
            if (isset($data["storage_path"])) $file->storage_path = $data["storage_path"];
            if (isset($data["mimetype"])) $file->mimetype = $data["mimetype"];
            if (isset($data["size"])) $file->size = $data["size"];
            if (isset($data["folder_id"])) $file->folder_id = $data["folder_id"];

            $file->save();

            if (isset($data["departements"])) {
                $file->departements()->sync($data['departements']);
            }

            return $file->fresh('departements');
        } catch (Throwable $e) {
            Log::error("Erreur SQL : Mise à jour du fichier $id échouée", [
                'message' => $e->getMessage(),
                'payload' => $data,
            ]);
            throw new PersistenceException("Erreur lors de la mise à jour des métadonnées du fichier.", 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        $file = $this->read($id);
        $file->delete();
        return true;
    }
    /**
     * @inheritDoc
     */
    public function archive(int $file_id): bool
    {
        return $this->setIsArchived($file_id, true);
    }

    /**
     * @inheritDoc
     */
    public function restore(int $file_id): bool
    {
        return $this->setIsArchived($file_id, false);
    }

    // --- RECHERCHE ---

    /**
     * @inheritDoc
     */
    public function performSearch(string $query, array $folderIds, bool $fromArchived = false): Collection
    {
        return File::search($query)
            ->whereIn('folder_id', $folderIds)
            ->where('is_archived', (int) $fromArchived)
            ->get();
    }

    // --- VERSIONS ---

    /**
     * @inheritDoc
     */
    public function findVersionsFromParent(int $parent_id): Collection
    {
        return Version::where('versionable_id', $parent_id)
            ->where("versionable_type", File::class)
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function findVersionWithParent(int $versionId): Version
    {
        return Version::with('versionable')->findOrFail($versionId);
    }

    // --- HELPERS PRIVÉS ---

    /**
     * Bascule l'état d'archivage d'un fichier.
     */
    private function setIsArchived(int $id, bool $is_archived): bool
    {
        $file = $this->read($id);

        try {
            $file->is_archived = $is_archived;
            return $file->save();
        } catch (Throwable $e) {
            Log::error("Erreur SQL : Changement état archivage fichier $id", [
                'message' => $e->getMessage(),
            ]);
            throw new PersistenceException("Erreur technique lors de la modification de l'état.", 0, $e);
        }
    }

    /**
     * Vérifie la disponibilité d'un nom dans un dossier (tous types confondus).
     */
    private function checkName(?int $folder_id, string $name, ?int $id = null): bool
    {
        $fileQuery = File::where('folder_id', $folder_id)
            ->where('name', $name)
            ->when($id, fn($q) => $q->where('id', '!=', $id));

        $docQuery = Document::where('folder_id', $folder_id)
            ->where('name', $name);

        return $fileQuery->exists() || $docQuery->exists();
    }
}
