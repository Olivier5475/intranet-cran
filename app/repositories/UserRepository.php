<?php

namespace App\repositories;

use App\Models\User;
use App\repositories\interfaces\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface {
    public function getUserByEmail(string $email) : ?User {
        return User::where('email', $email)->first();
    }
    public function createUser(array $data) {
        if(!isset($data["role"])) { $data['role'] = 'user'; }
        if(is_null($data['role'])) { $data['role'] = 'user'; }
        try {
            User::create($data);
        } catch (\Exception) {
            throw new \Exception("Error creating user");
        }
    }
    public function updateUser(int $id, array $data) {
        User::update($data);
    }
}
