<?php

namespace App\Services\Interfaces;

use App\DTO\FileDTO;
use App\Exception\{DiskWriteException, PersistenceException};
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\StreamedResponse;

interface FilesServiceInterface
{
    /**
     * Récupère un fichier par son identifiant unique.
     *
     * @param int $id Identifiant du fichier.
     * @return FileDTO
     * @throws \App\Exception\FileNotFoundException Si le fichier n'existe pas en base.
     */
    public function read(int $id): FileDTO;

    /**
     * Vérifie si l'utilisateur actuel a le droit de modifier le fichier.
     *
     * @param int $file_id
     * @return bool
     */
    public function hasEditAccess(int $file_id): bool;

    /**
     * Récupère l'ID du dossier parent d'un fichier.
     *
     * @param int $file_id
     * @return int
     */
    public function getParentId(int $file_id): int;

    /**
     * Génère une réponse pour télécharger la version actuelle du fichier.
     *
     * @param int $id
     * @return StreamedResponse
     * @throws FileNotFoundException Si le fichier physique est absent du disque.
     */
    public function download(int $id): StreamedResponse;

    /**
     * Génère une réponse pour télécharger une version spécifique (archivée) d'un fichier.
     *
     * @param int $id Identifiant de la version.
     * @return StreamedResponse
     * @throws FileNotFoundException Si le fichier de version est introuvable.
     */
    public function downloadVersion(int $id): StreamedResponse;

    /**
     * Crée un fichier : gère l'upload physique avec renommage sécurisé (UUID)
     * et l'enregistrement des métadonnées en base.
     *
     * @param array{folder_id: int, file: \Illuminate\Http\UploadedFile, name?: string} $data
     * @return FileDTO
     * @throws BadRequestException Si le fichier ou le dossier parent est manquant.
     * @throws DiskWriteException Si le stockage physique échoue.
     * @throws PersistenceException Si la transaction en base de données échoue.
     */
    public function create(array $data): FileDTO;

    /**
     * Met à jour un fichier (métadonnées ou remplacement du fichier physique).
     * En cas de remplacement, l'ancien fichier est supprimé après validation.
     *
     * @param int $id
     * @param array $data
     * @return FileDTO
     * @throws PersistenceException
     */
    public function update(int $id, array $data): FileDTO;

    /**
     * Archive un fichier (suppression logique).
     *
     * @param int $id
     * @return bool
     * @throws PersistenceException
     */
    public function delete(int $id): bool;

    /**
     * Restaure un fichier précédemment archivé.
     *
     * @param int $file_id
     * @return bool
     * @throws PersistenceException
     */
    public function restore(int $file_id): bool;
}
