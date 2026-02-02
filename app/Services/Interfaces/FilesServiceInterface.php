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
     * @return void
     * @throws FolderNotFoundException|AlreadyExistsException|DiskWriteException|PersistenceException
     */
    public function create(array $data) : void;

    /**
     * Fonction pour obtenir le storage_path d'un fichier selon son ID
     * @param $id l'id du fichier qu'on recherche
     * @param $folder_id l'id du dossier
     * @return FileDTO
     * @throws FileNotFoundException si le fichier n'est pas trouvé en BD
     * @throws BadRequestException si pas de folder_id ou de fichier
     */
    public function read($folder_id, $id) : FileDTO ;

    /**
     * @param int $folder_id
     * @param int $id
     * @param array $data
     * @return void
     * @throws PersistenceException|AlreadyExistsException|FileNotFoundException|BadRequestException|DiskWriteException
     */
    public function update(int $folder_id, int $id, array $data) : void;

    /**
     * @param int $folder_id ID du dossier
     * @param int $id ID du fichier
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
}
