<?php

namespace App\Repositories\Interfaces;

use App\Exception\FileNotFoundException;
use App\Exception\PersistenceException;
use App\Models\File;

interface FilesRepositoryInterface {

    /**
     * Créer un document.
     * @param array $data Les champs et leurs nouvelles valeurs (doivent être "fillable").
     * @return File Retourne le Document mis à jour
     * @throws PersistenceException En cas d'erreur de base de données.
     */
    public function create(array $data) : File;

    /**
     * Met à jour un document existant.
     * @param int $id L'ID du document à mettre à jour.
     * @param array $data Les champs et leurs nouvelles valeurs (doivent être "fillable").
     * @return File|bool Retourne le Document mis à jour, ou false si la mise à jour échoue.
     * @throws FileNotFoundException Si le document n'est pas trouvé.
     * @throws PersistenceException En cas d'erreur de base de données.
     */
    public function update(int $id, array $data) : File|bool;

    /**
     * Supprime un document existant.
     * @param int $id L'ID du document à mettre à jour.
     * @return bool retourne true si la suppression a réussi
     * @throws FileNotFoundException Si le file n'est pas trouvé.
     * @throws PersistenceException En cas d'erreur de base de données.
     */
    public function delete(int $id) : bool;

    /**
     * Récupère un fichier
     * @param int $id l'id du document à lire
     * @return File
     * @throws FileNotFoundException Si le file n'est pas trouvé.
     */
    public function read(int $id) : File ;
}
