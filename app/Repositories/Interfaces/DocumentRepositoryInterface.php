<?php

namespace App\Repositories\Interfaces;

use App\Exception\AlreadyExistsException;
use App\Exception\DocumentNotFoundException;
use App\Exception\PersistenceException;
use App\Models\Document;

interface DocumentRepositoryInterface {

    /**
     * @param array $data
     * @return Document
     * @throws PersistenceException|AlreadyExistsException
     */
    public function create(array $data) : Document;

    /**
     * @param int $id
     * @return Document
     * @throws DocumentNotFoundException
     */
    public function read(int $id) : Document;

    /**
     * @param int $id
     * @param array $data
     * @return Document
     * @throws DocumentNotFoundException|PersistenceException|AlreadyExistsException
     */
    public function update(int $id, array $data) : Document;

    /**
     * @param int $id
     * @return bool
     * @throws DocumentNotFoundException|PersistenceException
     */
    public function delete(int $id) : bool;

    /**
     * @return Document|null
     * @throws \Throwable
     */
    public function readRacineDoc() : ?Document;
}
