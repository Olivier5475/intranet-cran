<?php

namespace App\Services\Interface;

interface UserServiceInterface {
    public function handleUserInDatabase(array $data): void ;
    public function getUserByEmail(string $string);

}
