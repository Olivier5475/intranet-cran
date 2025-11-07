<?php

namespace App\Services\Interfaces;

use App\DTO\AttachmentDTO;
use App\Exception\AttachmentNotFoundException;
use App\Exception\DiskWriteException;
use App\Exception\DocumentNotFoundException;
use App\Exception\FolderNotFoundException;
use App\Exception\PersistenceException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Illuminate\Contracts\Filesystem ;

interface AttachmentServiceInterface {

    /**
     * Fonction pour mettre les fichiers de l'Attachment en storage et appeler le Repository pour l'enregistrement en BD
     * @param array $data données de création de l'Attachment
     * @return AttachmentDTO retourne un DTO de l'Attachment
     * @throws PersistenceException en cas d'erreur lors de la persistence en BD
     * @throws BadRequestException si pas de document_id ou de fichier
     * @throws FolderNotFoundException si le folder du document n'est pas trouvé
     * @throws DiskWriteException si une erreur survient lors de l'écriture du fichier
     * @throws DocumentNotFoundException
     */
    public function create(array $data) : AttachmentDTO;

    /**
     * Fonction pour lire un attachment (et avoir un DTO)
     * @param int $id ID de l'Attachment
     * @return AttachmentDTO DTO de l'Attachment correspondant
     * @throws BadRequestException si pas d'ID
     * @throws AttachmentNotFoundException si l'attachment n'est pas trouvé
     * @throws Filesystem\FileNotFoundException
     */
    public function read(int $id) : AttachmentDTO;

    /**
     * Fonction pour la MAJ d'un Attachment dans le Storage et appel du Repository pour la MAJ en BD
     * @param int $id
     * @param array $data
     * @return AttachmentDTO
     * @throws DiskWriteException si une erreur survient lors de l'écriture du fichier
     * @throws BadRequestException si pas de document_id ou de fichier
     * @throws PersistenceException dis les données n'ont pas pu être persisté en BD (throw par le Repository)
     * @throws AttachmentNotFoundException si l'attachment n'est pas trouvé (throw par le Repository)
     * @throws FileSystem\FileNotFoundException
     */
    public function update(int $id, array $data) : AttachmentDTO;

    /**
     * Fonction qui supprime un Attachment du storage et appel le Repository pour la suppression en BD
     * @param int $id ID de l'Attachment à supprimer
     * @return bool retourne true si la suppression à fonctionner
     * @throws BadRequestException si pas d'ID
     * @throws DiskWriteException si une erreur survient lors de l'écriture du fichier
     * @throws PersistenceException en cas d'erreur lors de la persistence en BD (throw par le Repository)
     * @throws AttachmentNotFoundException si Attachment introuvable (throw par le Repository)
     */
    public function delete(int $id) : bool;

    /**
     * Fonction pour récupérer le document d'un attachment
     * @param int $attachment_id ID de l'attachment
     * @return int id du document lié auquel est relié l'attachment
     */
    public function getDocumentId(int $attachment_id) : int;

    /**
     * Lance le telechargement d'un attachment
     * @param int $id l'ID de l'attachment
     * @throws Filesystem\FileNotFoundException
     * @throws AttachmentNotFoundException
     */
    public function download(int $id);

}
