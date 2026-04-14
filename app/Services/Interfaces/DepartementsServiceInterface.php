<?php

namespace App\Services\Interfaces;

use App\DTO\DepartementDTO;
use App\Exception\DepartementNotFoundException;
use App\Exception\PersistenceException;
use App\Exception\UserNotFoundException;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

interface DepartementsServiceInterface {
    /**
     * @return Collection
     */
    public function readAll(): Collection;


    /**
     * @param int $id
     * @param array $data
     * @return DepartementDTO
     * @throws PersistenceException|DepartementNotFoundException|BadRequestException
     */
    public function update(int $id, array $data): DepartementDTO;

    /**
     * @param array $data
     * @throws PersistenceException
     */
    public function create(array $data): void;

    /**
     * @param int $id
     * @return DepartementDTO
     * @throws DepartementNotFoundException|BadRequestException
     */
    public function readById(int $id): DepartementDTO;

    /**
     * @param int $id
     * @throws PersistenceException|DepartementNotFoundException|BadRequestException
     */
    public function delete(int $id): void;

    /**
     * @param $id
     * @return array
     * @throws DepartementNotFoundException
     */
    public function getUsers($id): Collection;

    /**
     * @param string $id
     * @param string $user_id
     * @return void
     * @throws UserNotFoundException
     * @throws DepartementNotFoundException
     * @throws PersistenceException
     */
    public function removeUser(string $id, string $user_id): void;
}
