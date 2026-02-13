<?php

namespace App\Services\Interfaces;

use App\DTO\FileDTO;
use App\DTO\VersionDTO;
use App\Exception\AlreadyExistsException;
use App\Exception\DiskWriteException;
use App\Exception\FileNotFoundException;
use App\Exception\FolderNotFoundException;
use App\Exception\PersistenceException;
use Illuminate\Contracts\Filesystem\FileNotFoundException as FilesystemNotFoundException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\StreamedResponse;

interface FilesServiceInterface
{
    /**
     * @param array $data {folder_id: int, file: UploadedFile, name: string|null, ...}
     * @throws FolderNotFoundException|AlreadyExistsException|DiskWriteException|PersistenceException|BadRequestException
     */
    public function create(array $data): FileDTO;

    /**
     * @param int $id
     * @throws FileNotFoundException|BadRequestException
     */
    public function read(int $id): FileDTO;

    /**
     * @param int $id
     * @param array $data
     * @throws PersistenceException|AlreadyExistsException|FileNotFoundException|DiskWriteException
     */
    public function update(int $id, array $data): FileDTO;

    /**
     * @param int $id
     * @throws PersistenceException|FileNotFoundException|DiskWriteException
     */
    public function delete(int $id): bool;

    /**
     * @param int $id
     * @throws FilesystemNotFoundException|FileNotFoundException
     */
    public function download(int $id): StreamedResponse;

    public function hasEditAccess(int $file_id): bool;

    /**
     * Restaure les attributs et le fichier physique d'une version donnée.
     * @param int $versionId
     * @throws PersistenceException|FileNotFoundException|\Exception
     */
    public function restoreFromVersionId(int $versionId): void;

    /**
     * @param int $parent_id ID du fichier parent
     * @return VersionDTO[]
     */
    public function readVersionsFromParent(int $parent_id): array;

    /**
     * @param int $id ID de la version
     */
    public function downloadVersion(int $id): StreamedResponse;
}
