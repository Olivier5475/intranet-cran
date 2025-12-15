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
     * @throws UserNotFoundException
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
     * @return void
     * @throws UserNotFoundException
     * @throws PersistenceException
     */
    public function updateUser(int $id, array $data) : void;

    /**
     * @return Collection
     */
    public function readAll() : Collection;

    /**
     * @param int $id
     * @return User
     */
    public function getUserById(int $id): User;

    /**
     * @param int $id
     * @return void
     * @throws PersistenceException la suppression n'a pas fonctionné
     */
    public function delete(int $id) : void;
}
