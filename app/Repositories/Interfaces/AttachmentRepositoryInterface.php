<?php

namespace App\Repositories\Interfaces;

use App\Exception\AttachmentNotFoundException;
use App\Exception\DocumentNotFoundException;
use App\Exception\PersistenceException;
use App\Models\Attachment;

interface AttachmentRepositoryInterface {

    /** Créé un attachment d'un document en BD
     * @param array $data données pour créer l'attachment
     * @return Attachment retourne l'Attachment créer
     * @throws DocumentNotFoundException si le document $data["id_document"] n'existe pas
     * @throws PersistenceException si la persistance des données ne fonctionne pas
     */
    public function create(array $data) : Attachment;

    /**
     * Fonction pour lire un Attachment depuis la BD
     * @param int $id ID de l'Attachment
     * @return Attachment retourne l'Attachment rechercher
     * @throws AttachmentNotFoundException si l'Attachment avec l'ID $id n'existe pas
     */
    public function read(int $id) : Attachment;

    /**
     * Fonction pour mettre à jour un Attachment en BD
     * @param int $id ID de l'Attachment a édité
     * @param array $data données pour mises à jour
     * @return Attachment|bool  retourne l'Attachment modifier ou false si la modification n'a pas fonctionné
     * @throws AttachmentNotFoundException si l'Attachment avec l'ID $id n'existe pas ou n'a pas été trouvé
     * @throws PersistenceException dis les données n'ont pas pu être persisté en BD
     */
    public function update(int $id, array $data) : Attachment|bool;

    /**
     * Fonction pour supprimer un Attachment de la BD
     * @param int $id ID de l'Attachment
     * @return bool renvoie true si la suppression a fonctionné
     * @throws AttachmentNotFoundException si l'Attachment avec l'ID $id n'existe pas ou n'a pas été trouvé
     * @throws PersistenceException dis les données n'ont pas pu être persisté en BD
     */
    public function delete(int $id) : bool;
}
