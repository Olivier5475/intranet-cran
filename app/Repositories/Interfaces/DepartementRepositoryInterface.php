<?php

namespace App\Repositories\Interfaces;

use App\Exception\DepartementNotFoundException;
use App\Exception\PersistenceException;
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
     * @return void
     * @throws DepartementNotFoundException
     * @throws PersistenceException
     */
    public function update(int $id, array $data) : void;

    /**
     * @return Collection
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
     * @return void
     * @throws PersistenceException la suppression n'a pas fonctionné
     */
    public function delete(int $id) : void;
}
