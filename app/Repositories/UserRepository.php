<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository implements Interfaces\UserRepositoryInterface {
    public function getUserByEmail(string $email) : ?User {
        return User::where('email', $email)->first();
    }
    public function createUser(array $data) {
        if(empty($data["role"])) { $data['role'] = 'user'; }
        try {
            $user = new User();
            $user->email = $data['email'];
            $user->nom = $data['nom'];
            $user->prenom = $data['prenom'];
            $user->role = $data['role'];
            $user->save();
        } catch (\Exception) {
            throw new \Exception("Error creating user");
        }
    }
    public function updateUser(int $id, array $data) {
        User::update($data);
    }
}
