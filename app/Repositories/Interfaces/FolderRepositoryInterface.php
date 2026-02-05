<?php

namespace App\Repositories\Interfaces;

use App\Exception\FolderNotFoundException;
use App\Exception\PersistenceException;
use App\Models\Folder;
use Illuminate\Support\Collection;

interface FolderRepositoryInterface
{
    public function getDescendantFolderIds(int $rootFolderId): array;

    /**
     * fonction qui lit un Folder depuis la BD
     * @param int $id ID du Folder qu'on recherche
     * @return Folder retourne le Folder correspondant à l'ID
     * @throws FolderNotFoundException si le folder n'est pas trouvé
     */
    public function read(int $id): Folder;

    /**
     * @param array $data
     * @return Folder
     * @throws PersistenceException
     */
    public function create(array $data): Folder;

    /**
     * @param int $id
     * @param array $data
     * @return Folder
     * @throws PersistenceException
     * @throws FolderNotFoundException
     */
    public function update(int $id, array $data): Folder;

    /**
     * @param int $id
     * @return bool
     * @throws FolderNotFoundException
     * @throws PersistenceException
     */
    public function delete(int $id): bool;

    /**
     * @return Collection
     * @throws FolderNotFoundException
     */
    public function getRacineChildren(): Collection;

    /**
     * @param int $id
     * @return Folder
     */
    public function getFolderWithContents(int $id): Folder;

    /**
     * @param int $id
     * @return Folder
     */
    public function getFolderWithParents(int $id): Folder;

}
