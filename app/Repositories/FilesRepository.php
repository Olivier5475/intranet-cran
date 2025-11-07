<?php

namespace App\Repositories;

use App\Exception\FileNotFoundException;
use App\Exception\PersistenceException;
use App\Models\File;
use Illuminate\Support\Facades\Log;

class FilesRepository implements Interfaces\FilesRepositoryInterface {

    /**
     * Créer un document.
     * @param array $data Les champs et leurs nouvelles valeurs (doivent être "fillable").
     * @return File Retourne le Document créé
     * @throws PersistenceException En cas d'erreur de base de données.
     */
    public function create(array $data): File {
        try {
            return File::create($data);
        } catch (\Throwable $e) {
            Log::error('Document creation error', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            throw new PersistenceException(message:"Could not create file.", previous:$e);
        }
    }

    public function read(int $id) : File {
        $file = File::find($id);
        if(!$file) {
            throw new FileNotFoundException("Le fichier avec l'id ".$id." n'existe pas.");
        }
        return  $file;
    }

    /**
     * Met à jour un document existant.
     * @param int $id L'ID du document à mettre à jour.
     * @param array $data Les champs et leurs nouvelles valeurs (doivent être "fillable").
     * @return File|bool Retourne le Document mis à jour, ou false si la mise à jour échoue.
     * @throws PersistenceException En cas d'erreur de base de données.
     * @throws FileNotFoundException si le fichier n'est pas trouvé
     */
    public function update(int $id, array $data): File|bool {
        $file = File::find($id);

        if (!$file) {
            throw new FileNotFoundException("File with ID $id not found.");
        }

        try {
            $file->fill($data);
            $result = $file->save();

            return $result ? $file : false;
        } catch (\Throwable $e) {
            Log::error('Document update failed for ID ' . $id, [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            throw new PersistenceException(message : "Could not update document with ID $id.", previous:$e);
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
}
