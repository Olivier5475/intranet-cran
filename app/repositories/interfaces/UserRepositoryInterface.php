<?php

namespace App\repositories\interfaces;

use App\Models\User;

interface UserRepositoryInterface {
    public function getUserByEmail(string $email) : ?User;
    public function createUser(array $data);
    public function updateUser(int $id, array $data);
}
