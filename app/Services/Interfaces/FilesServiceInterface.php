<?php

namespace App\Services\Interfaces;

use App\DTO\FileDTO;

use App\Exception\AlreadyExistsException;
use App\Exception\DiskWriteException;
use App\Exception\FileNotFoundException;
use App\Exception\FolderNotFoundException;
use App\Exception\PersistenceException;
use Illuminate\Contracts\Filesystem ;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

interface FilesServiceInterface {
    /**
     * @param array $data
     * @return FileDTO
     * @throws FolderNotFoundException|AlreadyExistsException|DiskWriteException|PersistenceException
     */
    public function create(array $data) : FileDTO;

    /**
     * Fonction pour obtenir le storage_path d'un fichier selon son ID
     * @param $id l'id du fichier qu'on recherche
     * @return FileDTO
     * @throws FileNotFoundException si le fichier n'est pas trouvé en BD
     * @throws BadRequestException si pas de folder_id ou de fichier
     */
    public function read($id) : FileDTO ;

    /**
     * @param int $id
     * @param array $data
     * @return FileDTO
     * @throws PersistenceException|AlreadyExistsException|FileNotFoundException|BadRequestException|DiskWriteException
     */
    public function update(int $id, array $data) : FileDTO;

    /**
     * @param int $id ID du fichier
     * @return bool
     * @throws BadRequestException
     * @throws PersistenceException
     * @throws FileNotFoundException
     * @throws Filesystem\FileNotFoundException
     * @throws DiskWriteException si une erreur survient lors de l'écriture du fichier
     */
    public function delete(int $id) : bool;

    /**
     * Lance le telechargement d'un fichier
     * @param int $id l'ID du fichier
     * @throws Filesystem\FileNotFoundException
     * @throws FileNotFoundException
     */
    public function download(int $id);

    /**
     * @param int $file_id
     * @return bool
     */
    public function hasEditAccess(int $file_id): bool;

}
