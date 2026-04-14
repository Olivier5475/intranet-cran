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

interface AttachmentServiceInterface
{
    /**
     * Récupère un attachement par son identifiant unique.
     *
     * @param int $id Identifiant de l'attachement.
     * @return AttachmentDTO
     * @throws AttachmentNotFoundException Si l'attachement n'existe pas en base.
     */
    public function read(int $id): AttachmentDTO;

    /**
     * Récupère l'identifiant du document parent d'un attachement.
     *
     * @param int $attachment_id Identifiant de l'attachement.
     * @return int ID du document parent.
     * @throws AttachmentNotFoundException Si l'attachement n'existe pas.
     */
    public function getDocumentId(int $attachment_id): int;

    /**
     * Crée un nouvel attachement : stocke le fichier physiquement et enregistre les meta-data en base.
     * Le chemin est construit dynamiquement : public/storage/{breadcrumbs_folders}/document_{ID}/uuid.ext
     *
     * @param array{document_id: int, uploaded_file: \Illuminate\Http\UploadedFile} $data
     * @return AttachmentDTO
     * @throws BadRequestException Si les paramètres requis sont manquants ou invalides.
     * @throws DocumentNotFoundException Si le document parent spécifié n'existe pas.
     * @throws DiskWriteException Si l'écriture du fichier sur le stockage échoue.
     * @throws PersistenceException Si l'enregistrement en base de données échoue (avec nettoyage du fichier).
     */
    public function create(array $data): AttachmentDTO;

    /**
     * Met à jour les informations d'un attachement (principalement le nom ou le dossier parent).
     *
     * @param int $id Identifiant de l'attachement.
     * @param array $data Données à mettre à jour.
     * @return AttachmentDTO
     * @throws AttachmentNotFoundException Si l'attachement n'existe pas.
     * @throws PersistenceException En cas d'erreur lors de la sauvegarde.
     */
    public function update(int $id, array $data): AttachmentDTO;

    /**
     * Prépare une réponse de streaming pour le téléchargement du fichier.
     *
     * @param int $id Identifiant de l'attachement.
     * @return StreamedResponse
     * @throws AttachmentNotFoundException Si l'entrée en base n'existe pas.
     * @throws FileNotFoundException Si le fichier est référencé en base mais absent du disque.
     */
    public function download(int $id): StreamedResponse;

    /**
     * Supprime définitivement l'attachement de la base de données et du disque.
     *
     * @param int $id Identifiant de l'attachement.
     * @return bool True si la suppression totale a réussi.
     * @throws AttachmentNotFoundException Si l'attachement n'existe pas.
     * @throws PersistenceException Si la transaction échoue ou si le fichier ne peut être supprimé.
     */
    public function delete(int $id): bool;
}
