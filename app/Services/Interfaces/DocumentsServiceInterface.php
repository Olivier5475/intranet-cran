<?php

namespace App\Services\Interfaces;
use App\DTO\DocumentDTO;
use App\DTO\DocumentViewDTO;
use App\Exception\AttachmentNotFoundException;
use App\Exception\DiskWriteException;
use App\Exception\DocumentNotFoundException;
use App\Exception\FolderNotFoundException;
use App\Exception\PersistenceException;
use App\Exception\ServerException;
use Illuminate\Contracts\Filesystem;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Throwable;

interface DocumentsServiceInterface {

    /**
     * @param $id
     * @return DocumentViewDTO
     * @throws BadRequestException si pas d'ID
     * @throws DocumentNotFoundException
     * @throws Filesystem\FileNotFoundException
     */
    public function read($id) : DocumentViewDTO;

    /**
     * Fonction de création d'un Document
     * Vérifie les données
     * Lance la creation en BD avec le Repository*
     * Lance la creation attachment
     * @param array $data
     * @return DocumentViewDTO
     * @throws BadRequestException
     * @throws PersistenceException
     * @throws DiskWriteException
     * @throws FolderNotFoundException
     * @throws Filesystem\FileNotFoundException
     * @throws InternalErrorException
     * @throws FolderNotFoundException
     * @throws DiskWriteException
     * @throws PersistenceException
     */
    public function create(array $data) : DocumentViewDTO;

    /**
     * @param int $id
     * @param array $data
     * @return DocumentViewDTO|bool
     * @throws DocumentNotFoundException
     * @throws PersistenceException
     * @throws DiskWriteException
     * @throws AttachmentNotFoundException
     * @throws FolderNotFoundException
     * @throws Throwable
     * @throws Filesystem\FileNotFoundException
     */
    public function update(int $id, array $data) : DocumentViewDTO|bool;

    /**
     * @param int $folder_id
     * @param int $id
     * @return bool
     * @throws DocumentNotFoundException
     * @throws BadRequestException si pas d'ID
     * @throws DiskWriteException si une erreur survient lors de l'écriture du fichier
     * @throws PersistenceException en cas d'erreur lors de la persistence en BD (throw par le Repository)
     * @throws AttachmentNotFoundException si Attachment introuvable (throw par le Repository)
     */
    public function delete(int $folder_id, int $id) : bool;

    /**
     * Fonction permettant l'obtention du premier document à la racine
     * @return DocumentViewDTO|null
     * @throws ServerException
     */
    public function readRacineDoc() : ?DocumentViewDTO;
}
