<?php

namespace App\Repositories\Interfaces;

use App\Exception\PersistenceException;
use App\Exception\UserNotFoundException;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface {

    /**
     * @param string $email
     * @return User|null
     */
    public function getUserByEmail(string $email) : ?User;

    /**
     * @param array $data
     * @return User
     * @throws PersistenceException
     */
    public function createUser(array $data) : User;

    /**
     * @param int $id
     * @param array $data
     * @throws UserNotFoundException
     * @throws PersistenceException
     */
    public function update(int $id, array $data) : void;

    /**
     * @return Collection<int, User>
     */
    public function readAll() : Collection;

    /**
     * @param int $id
     * @return User
     * @throws UserNotFoundException
     */
    public function getUserById(int $id): User;

    /**
     * @param int $id
     * @throws UserNotFoundException
     * @throws PersistenceException
     */
    public function delete(int $id) : void;
}
