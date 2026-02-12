<?php

namespace App\Services\Interfaces;

use App\DTO\AuthDTO;
use App\Exception\PersistenceException;
use App\Exception\UserNotFoundException;

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

    /**
     * @param int $id
     * @param array $data
     * @return void
     * @throws UserNotFoundException
     * @throws PersistenceException
     */
    public function update(int $id, array $data) : void;
    public function readById($id) : AuthDTO ;
    public function delete(int $id) : void;
    public function readAll();
    public function getRole();
    public function isAdmin();

    /**
     * @param string $returnUrl
     * @return void
     */
    public function logout(string $returnUrl) : void ;
}
