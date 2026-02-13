<?php

namespace App\Services\Interfaces;

use App\DTO\AttachmentDTO;
use App\Exception\AttachmentNotFoundException;
use App\Exception\DiskWriteException;
use App\Exception\DocumentNotFoundException;
use App\Exception\PersistenceException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\StreamedResponse;

interface AttachmentServiceInterface {

    /**
     * Enregistre le fichier physiquement et crée l'entrée en base de données.
     * * @param array $data {document_id: int, uploaded_file: UploadedFile}
     * @return AttachmentDTO
     * @throws BadRequestException Si des données obligatoires manquent ou sont invalides.
     * @throws DocumentNotFoundException Si le document parent n'existe pas.
     * @throws DiskWriteException Si l'écriture sur le disque échoue.
     * @throws PersistenceException En cas d'erreur lors de l'enregistrement SQL.
     */
    public function create(array $data) : AttachmentDTO;

    /**
     * Récupère un attachement sous forme de DTO.
     * * @param int $id
     * @return AttachmentDTO
     * @throws AttachmentNotFoundException Si l'ID n'existe pas.
     * @throws FileNotFoundException Si le fichier est référencé en BD mais absent du disque.
     */
    public function read(int $id) : AttachmentDTO;

    /**
     * Met à jour les métadonnées d'un attachement.
     * * @param int $id
     * @param array $data
     * @return AttachmentDTO
     * @throws AttachmentNotFoundException
     * @throws PersistenceException
     */
    public function update(int $id, array $data) : AttachmentDTO;

    /**
     * Supprime le fichier physique et l'entrée en base de données.
     * * @param int $id
     * @return bool
     * @throws AttachmentNotFoundException
     * @throws PersistenceException
     */
    public function delete(int $id) : bool;

    /**
     * Récupère l'ID du document lié à l'attachement.
     * * @param int $attachment_id
     * @return int
     * @throws AttachmentNotFoundException
     */
    public function getDocumentId(int $attachment_id) : int;

    /**
     * Prépare la réponse de téléchargement pour le navigateur.
     * * @param int $id
     * @return StreamedResponse
     * @throws AttachmentNotFoundException
     * @throws FileNotFoundException
     */
    public function download(int $id): StreamedResponse;
}
