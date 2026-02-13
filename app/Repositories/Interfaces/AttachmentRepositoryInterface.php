<?php

namespace App\Repositories\Interfaces;

use App\Exception\AttachmentNotFoundException;
use App\Exception\PersistenceException;
use App\Models\Attachment;

interface AttachmentRepositoryInterface {

    /**
     * @param array $data
     * @return Attachment
     * @throws PersistenceException Si l'insertion SQL échoue.
     */
    public function create(array $data) : Attachment;

    /**
     * @param int $id
     * @return Attachment
     * @throws AttachmentNotFoundException Si l'ID n'existe pas.
     */
    public function read(int $id) : Attachment;

    /**
     * @param int $id
     * @param array $data
     * @return Attachment
     * @throws AttachmentNotFoundException
     * @throws PersistenceException
     */
    public function update(int $id, array $data) : Attachment;

    /**
     * @param int $id
     * @return bool
     * @throws AttachmentNotFoundException
     * @throws PersistenceException
     */
    public function delete(int $id) : bool;
}
