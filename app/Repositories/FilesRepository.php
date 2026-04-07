<?php

namespace App\Repositories;

use App\Exception\FileNotFoundException;
use App\Exception\PersistenceException;
use App\Exception\AlreadyExistsException;
use App\Models\Document;
use App\Models\File;
use App\Models\Version;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class FilesRepository implements Interfaces\FilesRepositoryInterface {

    public function create(array $data): File {
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

    public function read(int $id) : File {
        $file = File::with("departements")->find($id);

        if (!$file) {
            throw new FileNotFoundException("Le fichier avec l'ID $id est introuvable.");
        }
        return $file;
    }

    public function update(int $id, array $data): File {
        $file = $this->read($id);

        if (isset($data["name"]) && $this->checkName($file->folder_id, $data["name"], $id)) {
            throw new AlreadyExistsException("Le nouveau nom est déjà utilisé.");
        }

        try {
            if (isset($data["name"])) $file->name = e($data["name"]);
            if (isset($data["storage_path"])) $file->storage_path = $data["storage_path"];
            if (isset($data["mimetype"])) $file->mimetype = $data["mimetype"];
            if (isset($data["size"])) $file->size = $data["size"];

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
     * @throws PersistenceException
     * @throws FileNotFoundException
     */
    private function setIsArchived(int $id, bool $is_archived) : bool {
        $file = $this->read($id);

        try {
            $file->is_archived = $is_archived;
            return $file->save();
        } catch (Throwable $e) {
            Log::error("Erreur SQL : Suppression du fichier $id échouée", [
                'message' => $e->getMessage(),
            ]);
            throw new PersistenceException("Erreur technique lors de la suppression en base de données.", 0, $e);
        }
    }

    public function delete(int $id) : bool {
        return $this->setIsArchived($id, true);
    }

    public function restore(int $file_id): bool
    {
        return $this->setIsArchived($file_id, false);
    }

    private function checkName(?int $folder_id, string $name, ?int $id = null): bool {
        // Gestion des nuls pour folder_id (racine)
        $fileQuery = File::where('folder_id', $folder_id)
            ->where('name', $name)
            ->when($id, fn($q) => $q->where('id', '!=', $id));

        $docQuery = Document::where('folder_id', $folder_id)
            ->where('title', $name);

        return $fileQuery->exists() || $docQuery->exists();
    }

    public function findVersionWithParent(int $versionId): Version {
        return Version::with('versionable')->findOrFail($versionId);
    }

    public function findVersionsFromParent(int $parent_id): Collection {
        return Version::where('versionable_id', $parent_id)
            ->where("versionable_type", File::class)
            ->orderByDesc('created_at')
            ->get();
    }
}
