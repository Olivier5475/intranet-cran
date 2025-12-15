<?php

namespace App\Services\Interfaces;

use App\DTO\AuthDTO;
use App\Exception\PersistenceException;

interface UserServiceInterface {

    /**
     * @param array $data
     * @return void
     * @throws PersistenceException Erreur de persistance du nouvel utilisateur
     */
    public function handleUserInDatabase(array $data): void ;
    public function getUserByEmail(string $string);

    /**
     * Fonction pour obtenir l'id de l'utilisateur connecté
     * @return int
     */
    public function getCurrentUserId() : int;

    public function update(mixed $id, array $data);


    public function readById($id) : AuthDTO ;

    public function delete(int $id);

    public function readAll();

}
