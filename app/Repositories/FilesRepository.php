<?php

namespace App\Repositories;

use App\Exception\FileNotFoundException;
use App\Exception\PersistenceException;
use App\Models\Document;
use App\Models\File;
use App\Exception\AlreadyExistsException;
use App\Models\Version;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class FilesRepository implements Interfaces\FilesRepositoryInterface {

    /**
     * Créer un document.
     * @param array $data Les champs et leurs nouvelles valeurs (doivent être "fillable").
     * @return File
     * @throws PersistenceException|AlreadyExistsException En cas d'erreur de base de données.
     */
    public function create(array $data): File {
        if($this->checkName($data["folder_id"], $data["name"])){
            throw new AlreadyExistsException();
        }
        try {
            $file = new File();
            $file->name = e($data['name']);
            $file->folder_id = $data['folder_id'];
            $file->user_id = $data['user_id'];
            $file->storage_path = $data['storage_path'];
            $file->mimetype = $data['mimetype'];
            $file->size = $data['size'];
            $file->save();
            $file->departements()->attach($data['departements']);
            return $file;
        } catch (\Throwable $e) {
            Log::error('Document creation error', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            throw new PersistenceException(message:"Could not create file.", previous:$e);
        }
    }


    public function read(int $id) : File {
        try {
            $file = File::with("departements")->find($id);
        } catch (\Throwable $e) {
            Log::error('File read fatal error', []);
            throw $e;
        }
        if(!$file) {
            throw new FileNotFoundException("Le fichier avec l'id ".$id." n'existe pas.");
        }
        return  $file;
    }

    /**
     * Met à jour un document existant.
     * @param int $id L'ID du document à mettre à jour.
     * @param array $data Les champs et leurs nouvelles valeurs (doivent être "fillable").
     * @return File
     * @throws PersistenceException En cas d'erreur de base de données.
     * @throws FileNotFoundException|AlreadyExistsException si le fichier n'est pas trouvé
     */
    public function update(int $id, array $data): File {
        $file = File::with("departements")->find($id);

        if (!$file) {
            throw new FileNotFoundException("File with ID $id not found.");
        }

        if($this->checkName($file->folder_id, $data["name"], $id)){
            throw new AlreadyExistsException();
        }
        try {
            $file->name = e($data["name"]);
            if(isset($data["storage_path"]) && $data["storage_path"]){
                $file->storage_path = $data["storage_path"];
            }
            if(isset($data["mimetype"]) && $data["mimetype"]){
                $file->mimetype = $data["mimetype"];
            }
            if(isset($data["size"]) && $data["size"]){
                $file->size = $data["size"];
            }
            $file->save();
            if(isset($data["departements"])){
                $file->departements()->sync($data['departements']);
            }
            return $file;
        } catch (\Throwable $e) {
            Log::error('Document update failed for ID ' . $id, [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            throw new PersistenceException(message : "Could not update file with ID $id.", previous:$e);
        }
    }

    /**
     * Supprime un document existant.
     * @param int $id L'ID du document à mettre à jour.
     * @return bool retourne true si la suppression a réussi
     * @throws FileNotFoundException Si le document n'est pas trouvé.
     * @throws PersistenceException En cas d'erreur de base de données.
     */
    public function delete(int $id) : bool {
        $file = File::find($id);

        if (!$file) {
            throw new FileNotFoundException("File with ID $id not found.");
        }

        try {
            $file->delete();
            return true;
        } catch (\Throwable $e) {
            Log::error('File delete failed for ID ' . $id, [
                'error' => $e->getMessage(),
            ]);

            throw new PersistenceException(message : "Could not delete file with ID $id.", previous:$e);
        }
    }

    private function checkName(int $folder_id, string $name, ?int $id = null): bool {
        $fileQuery = File::where('folder_id', "=", $folder_id)
            ->where('name', "=", $name)
            ->when($id, fn($q) => $q->where('id', '!=', $id));

        $docQuery = Document::where('folder_id', "=", $folder_id)
            ->where('title', "=", $name);

        return $fileQuery->exists() || $docQuery->exists();
    }

    public function findVersionWithParent(int $versionId): Version
    {
        return Version::with('versionable')->findOrFail($versionId);
    }

    public function findVersionsFromParent(int $parent_id): Collection
    {
        return Version::where('versionable_id', '=', $parent_id)->where("versionable_type", "=", File::class)->get();
    }
}
