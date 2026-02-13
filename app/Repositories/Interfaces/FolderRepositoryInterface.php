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
     * @throws FolderNotFoundException|PersistenceException
     */
    public function delete(int $id): void;

    /**
     * @return Collection<int, Folder>
     */
    public function getRacineChildren(): Collection;

    public function getFolderWithContents(int $id): Folder;

    public function getFolderWithParents(int $id): Folder;
}
