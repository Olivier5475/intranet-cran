<?php

namespace App\Repositories;

use App\Exception\PersistenceException;
use App\Exception\UserNotFoundException;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserRepository implements Interfaces\UserRepositoryInterface {
    public function getUserByEmail(string $email) : ?User {
        $user =  User::where('email', $email)->first();
        return $user;
    }

    /**
     * @throws \Exception
     */
    public function createUser(array $data): User {
        if(empty($data["role"])) { $data['role'] = 'user'; }
        try {
            $user = new User();
            $user->email = $data['email'];
            $user->nom = $data['nom'];
            $user->prenom = $data['prenom'];
            $user->role = $data['role'];
            $user->save();
            return $user;
        } catch (\Exception) {
            throw new PersistenceException("Error creating user");
        }
    }

    public function updateUser(int $id, array $data): void {
        $user = User::find($id);
        if(empty($user)) {
            throw new UserNotFoundException("Utilisateur introuvable, id : " . $id);
        }
        try {
            $user->nom = $data['nom'];
            $user->prenom = $data['prenom'];
            $user->email = $data['email'];
            $user->role = $data['role'];
            $user->save();
        } catch (\Throwable) {
            throw new PersistenceException("Erreur lors de la mise à jour de l'utilisateur.");
        }
    }

    public function readAll(): Collection {
        return User::all();
    }

    public function getUserById(int $id) : User {
        return User::find($id);
    }

    public function delete(int $id) : void {
        try {
            $user = User::find($id);
            $user->delete();
        } catch (\Throwable) {
           throw new PersistenceException();
        }
    }
}
