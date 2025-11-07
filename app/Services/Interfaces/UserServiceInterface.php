<?php

namespace App\Services\Interfaces;

interface UserServiceInterface {
    public function handleUserInDatabase(array $data): void ;
    public function getUserByEmail(string $string);

    /**
     * Fonction pour obtenir l'id de l'utilisateur connecté
     * @return int
     */
    public function getCurrentUserId() : int;

}
