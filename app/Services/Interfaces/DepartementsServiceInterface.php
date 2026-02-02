<?php

namespace App\Services\Interfaces;

use App\DTO\DepartementDTO;
use App\Exception\DepartementNotFoundException;
use App\Exception\PersistenceException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

interface DepartementsServiceInterface {
    public function readAll():array;
    public function departementsIDs($departements):array;

    /**
     * @param int $id
     * @param array $data
     * @return DepartementDTO
     * @throws PersistenceException | DepartementNotFoundException | BadRequestException
     */
    public function update(int $id, array $data) : DepartementDTO;

    /**
     * @param array $data
     * @return void
     * @throws PersistenceException | BadRequestException
     */
    public function create(array $data) : void;

    /**
     * @param int $id
     * @return mixed
     * @throws DepartementNotFoundException | BadRequestException
     */
    public function readById(int $id);

    /**
     * @param int $id
     * @return void
     * @throws PersistenceException | DepartementNotFoundException | BadRequestException
     */
    public function delete(int $id) : void;
}
