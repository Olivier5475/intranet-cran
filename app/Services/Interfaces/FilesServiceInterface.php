<?php

namespace App\Services\Interfaces;

use App\DTO\FileDTO;
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
     * @throws FolderNotFoundException
     * @throws DiskWriteException
     * @throws PersistenceException
     */
    public function create(array $data) : FileDTO;

    /**
     * Fonction pour obtenir le storage_path d'un fichier selon son ID
     * @param $id l'id du fichier qu'on recherche
     * @return string|null
     * @throws FileNotFoundException si le fichier n'est pas trouvé en BD
     * @throws Filesystem\FileNotFoundException si le fichier n'est pas trouvé en storage
     * @throws FolderNotFoundException si folder inexistant
     * @throws BadRequestException si pas de folder_id ou de fichier
     */
    public function read($id) : ?string ;

    /**
     * @param int $id
     * @param array $data
     * @return FileDTO|bool
     * @throws PersistenceException
     * @throws FileNotFoundException
     * @throws BadRequestException
     * @throws DiskWriteException si une erreur survient lors de l'écriture du fichier
     */
    public function update(int $id, array $data) : FileDTO|bool;

    /**
     * @param int $id
     * @return bool
     * @throws BadRequestException
     * @throws PersistenceException
     * @throws FileNotFoundException
     * @throws Filesystem\FileNotFoundException
     * @throws DiskWriteException si une erreur survient lors de l'écriture du fichier
     */
    public function delete(int $folder_id, int $id) : bool;

    /**
     * Lance le telechargement d'un attachment
     * @param int $id l'ID de l'attachment
     * @throws Filesystem\FileNotFoundException
     * @throws FileNotFoundException
     */
    public function download(int $id);

    /**
     * Fonction pour obtenir le storage_path d'un fichier selon son ID
     * @param $id l'id du fichier qu'on recherche
     * @return string
     * @throws FileNotFoundException si le fichier n'est pas trouvé en BD
     * @throws BadRequestException si pas de folder_id ou de fichier
     */
    public function readName(int $id): string;
}
