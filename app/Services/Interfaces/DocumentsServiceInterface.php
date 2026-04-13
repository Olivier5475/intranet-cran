<?php

namespace App\Services\Interfaces;

use App\DTO\DocumentViewDTO;
use App\Exception\AttachmentNotFoundException;
use App\Exception\DiskWriteException;
use App\Exception\DocumentNotFoundException;
use App\Exception\PersistenceException;
use App\Exception\ServerException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

interface DocumentsServiceInterface {

    /**
     * @param int $id
     * @return DocumentViewDTO
     * @throws DocumentNotFoundException
     * @throws FileNotFoundException
     */
    public function read(int $id) : DocumentViewDTO;

    /**
     * Crée un document et ses attachements associés (Transactionnel).
     * @param array $data
     * @return DocumentViewDTO
     * @throws BadRequestException|PersistenceException|DiskWriteException
     */
    public function create(array $data) : DocumentViewDTO;

    /**
     * Met à jour un document et synchronise ses attachements (Transactionnel).
     * @param int $id
     * @param array $data
     * @return DocumentViewDTO
     * @throws DocumentNotFoundException|PersistenceException|DiskWriteException|AttachmentNotFoundException
     */
    public function update(int $id, array $data) : DocumentViewDTO;

    /**
     * Supprime le document et tous ses attachements liés.
     * @param int $id
     * @return bool
     * @throws DocumentNotFoundException|PersistenceException
     */
    public function delete(int $id) : bool;

    /**
     * Récupère le document racine de l'application.
     * @return DocumentViewDTO|null
     * @throws ServerException
     */
    public function readRacineDoc() : ?DocumentViewDTO;

    /**
     * Vérifie les droits de modification de l'utilisateur sur un document.
     * @param int $document_id
     * @return bool
     */
    public function hasEditAccess(int $document_id) : bool;

    /**
     * @param int $document_id
     * @return bool
     * @throws DocumentNotFoundException|PersistenceException
     */
    public function restore(int $document_id): bool;

    public function getParentId(int $folder_id): int;
}
