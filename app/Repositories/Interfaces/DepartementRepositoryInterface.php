<?php

namespace App\Repositories\Interfaces;

use App\Exception\DepartementNotFoundException;
use App\Exception\PersistenceException;
use App\Exception\UserNotFoundException;
use App\Models\Departement;
use Illuminate\Database\Eloquent\Collection;

interface DepartementRepositoryInterface {

    /**
     * @param array $data
     * @return Departement
     * @throws PersistenceException
     */
    public function create(array $data) : Departement;

    /**
     * @param int $id
     * @param array $data
     * @throws DepartementNotFoundException
     * @throws PersistenceException
     */
    public function update(int $id, array $data) : void;

    /**
     * @return Collection<int, Departement>
     */
    public function readAll() : Collection;

    /**
     * @param int $id
     * @return Departement
     * @throws DepartementNotFoundException
     */
    public function getById(int $id): Departement;

    /**
     * @param int $id
     * @throws DepartementNotFoundException
     * @throws PersistenceException
     */
    public function delete(int $id) : void;

    /**
     * @param $id
     * @return Collection
     * @throws DepartementNotFoundException
     */
    public function readUsers($id) : Collection;

    /**
     * Retire un utilisateur d'un departement
     * @param string $id ID du departement
     * @param string $user_id ID de l'utilisateur
     * @return void
     * @throws UserNotFoundException
     * @throws DepartementNotFoundException
     * @throws PersistenceException
     */
    public function removeUser(string $id, string $user_id): void;


}
