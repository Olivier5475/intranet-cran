<?php

namespace App\Repositories\Interfaces;

use App\Exception\AlreadyExistsException;
use App\Exception\FileNotFoundException;
use App\Exception\PersistenceException;
use App\Models\File;
use App\Models\Version;
use Illuminate\Database\Eloquent\Collection;

interface FilesRepositoryInterface {

    /**
     * @param array $data
     * @return File
     * @throws PersistenceException|AlreadyExistsException
     */
    public function create(array $data) : File;

    /**
     * @param int $id
     * @param array $data
     * @return File
     * @throws FileNotFoundException|PersistenceException|AlreadyExistsException
     */
    public function update(int $id, array $data) : File;

    /**
     * @param int $id
     * @return bool
     * @throws FileNotFoundException|PersistenceException
     */
    public function delete(int $id) : bool;

    /**
     * @param int $id
     * @return File
     * @throws FileNotFoundException
     */
    public function read(int $id) : File ;

    /**
     * @param int $versionId
     * @return Version
     */
    public function findVersionWithParent(int $versionId): Version;

    /**
     * @param int $parent_id
     * @return Collection<int, Version>
     */
    public function findVersionsFromParent(int $parent_id): Collection;

    /**
     * @param int $file_id
     * @return bool
     * @throws PersistenceException
     * @throws FileNotFoundException
     */
    public function restore(int $file_id): bool;
}
