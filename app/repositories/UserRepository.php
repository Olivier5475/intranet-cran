<?php

namespace App\repositories;

use App\Models\User;
use App\repositories\interfaces\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface {
    public function getUserByEmail(string $email) : ?User {
        return User::where('email', $email)->first();
    }
    public function createUser(array $data) {
        if(is_null($data['role'])) { $data['role'] = 'user'; }
        User::create($data);
    }
    public function updateUser(int $id, array $data) {
        User::update($data);
    }
}
