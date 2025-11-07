<?php

namespace App\Repositories\Interfaces;

use App\Exception\DocumentNotFoundException;
use App\Exception\PersistenceException;
use App\Models\Document;

interface DocumentRepositoryInterface {

    /**
     * Créer un document.
     * @param array $data Les champs et leurs nouvelles valeurs (doivent être "fillable").
     * @return Document|bool Retourne le Document mis à jour, ou false si la création échoue.
     * @throws PersistenceException En cas d'erreur de base de données.
     */
    public function create(array $data) : Document|bool;

    /**
     * Lit un document existant.
     * @param int $id L'ID du document à mettre à jour.
     * @return Document Retourne le Document avec l'ID $id
     * @throws DocumentNotFoundException Si le document n'est pas trouvé.
     */
    public function read(int $id) : Document;

    /**
     * Met à jour un document existant.
     * @param int $id L'ID du document à mettre à jour.
     * @param array $data Les champs et leurs nouvelles valeurs (doivent être "fillable").
     * @return Document|bool Retourne le Document mis à jour, ou false si la mise à jour échoue.
     * @throws DocumentNotFoundException Si le document n'est pas trouvé.
     * @throws PersistenceException En cas d'erreur de base de données.
     */
    public function update(int $id, array $data) : Document|bool;

    /**
     * Supprime un document existant.
     * @param int $id L'ID du document à mettre à jour.
     * @return bool retourne true si la suppression a réussi
     * @throws DocumentNotFoundException Si le document n'est pas trouvé.
     * @throws PersistenceException En cas d'erreur de base de données.
     */
    public function delete(int $id) : bool;
}
