<?php

namespace App\Repositories\Interfaces;

use App\Exception\FolderNotFoundException;
use App\Exception\PersistenceException;
use App\Models\Folder;
use Illuminate\Support\Collection;

interface FolderRepositoryInterface
{
    /**
     * Récupère récursivement tous les IDs des dossiers descendants.
     */
    public function getDescendantFolderIds(int $rootFolderId): array;

    /**
     * @throws FolderNotFoundException
     */
    public function read(int $id): Folder;

    /**
     * @throws PersistenceException
     */
    public function create(array $data): Folder;

    /**
     * @throws PersistenceException|FolderNotFoundException
     */
    public function update(int $id, array $data): Folder;

    /**
     * @param int $id
     * @return bool
     * @throws FolderNotFoundException|PersistenceException
     */
    public function delete(int $id): bool;

    /**
     * @return Collection<int, Folder>
     */
    public function getRacineChildren(): Collection;

    public function getFolderWithContents(int $id, bool $archived): Folder;

    public function getFolderWithParents(int $id): Folder;

    /**
     * @param int $folder_id
     * @return bool
     * @throws FolderNotFoundException
     * @throws PersistenceException
     */
    public function restore(int $folder_id): bool;
}
