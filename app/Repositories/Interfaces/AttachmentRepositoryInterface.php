<?php

namespace App\Repositories\Interfaces;

use App\Models\Attachment;
use App\Exception\{AttachmentNotFoundException, PersistenceException};

interface AttachmentRepositoryInterface
{
    /**
     * Insère un nouvel enregistrement d'attachement en base de données.
     *
     * @param array $data Attributs de l'attachement (document_id, name, storage_path, size, mimetype).
     * @return Attachment
     * @throws PersistenceException Si l'insertion SQL échoue.
     */
    public function create(array $data): Attachment;

    /**
     * Récupère un attachement par son identifiant unique.
     *
     * @param int $id
     * @return Attachment
     * @throws AttachmentNotFoundException Si aucun enregistrement n'est trouvé.
     */
    public function read(int $id): Attachment;

    /**
     * Met à jour les colonnes d'un attachement existant.
     *
     * @param int $id
     * @param array $data Données à modifier.
     * @return Attachment L'instance mise à jour.
     * @throws AttachmentNotFoundException Si l'attachement n'existe pas.
     * @throws PersistenceException Si la mise à jour SQL échoue.
     */
    public function update(int $id, array $data): Attachment;

    /**
     * Supprime physiquement un enregistrement d'attachement de la base.
     *
     * @param int $id
     * @return bool True en cas de succès.
     * @throws AttachmentNotFoundException Si l'attachement n'existe pas.
     * @throws PersistenceException Si la suppression SQL échoue.
     */
    public function delete(int $id): bool;
}
