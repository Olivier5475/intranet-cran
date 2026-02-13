<?php

namespace App\Repositories;

use App\Exception\PersistenceException;
use App\Exception\UserNotFoundException;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class UserRepository implements Interfaces\UserRepositoryInterface {

    public function getUserByEmail(string $email) : ?User {
        return User::where('email', $email)->first();
    }

    public function createUser(array $data): User {
        try {
            $user = new User();
            $user->email = $data['email'];
            $user->nom = $data['nom'];
            $user->prenom = $data['prenom'];
            $user->role = $data['role'] ?? 'user';
            $user->save();

            if (!empty($data['departements'])) {
                $user->departements()->attach($data['departements']);
            }

            return $user;
        } catch (Throwable $t) {
            Log::error("Erreur SQL lors de la création de l'utilisateur", [
                'email' => $data['email'] ?? 'N/A',
                'message' => $t->getMessage()
            ]);
            throw new PersistenceException("Erreur lors de la création de l'utilisateur en base de données.", 0, $t);
        }
    }

    public function updateUser(int $id, array $data): void {
        $user = User::find($id);

        if (!$user) {
            throw new UserNotFoundException("Mise à jour impossible : utilisateur ID $id introuvable.");
        }

        try {
            $user->update([
                'nom'    => $data['nom'],
                'prenom' => $data['prenom'],
                'email'  => $data['email'],
                'role'   => $data['role'],
            ]);

            if (isset($data['departements'])) {
                $user->departements()->sync($data['departements']);
            }
        } catch (Throwable $t) {
            Log::error("Erreur SQL lors de la mise à jour de l'utilisateur $id", [
                'message' => $t->getMessage(),
                'data' => $data
            ]);
            throw new PersistenceException("Erreur de persistence lors de la modification de l'utilisateur.");
        }
    }

    public function readAll(): Collection {
        return User::all();
    }

    public function getUserById(int $id) : User {
        $user = User::find($id);
        if (!$user) {
            throw new UserNotFoundException("Utilisateur ID $id introuvable.");
        }
        return $user;
    }

    public function delete(int $id) : void {
        $user = User::find($id);

        if (!$user) {
            throw new UserNotFoundException("Suppression impossible : utilisateur ID $id introuvable.");
        }

        try {
            $user->delete();
        } catch (Throwable $t) {
            Log::error("Échec de la suppression SQL de l'utilisateur $id", [
                'message' => $t->getMessage()
            ]);
            throw new PersistenceException("Erreur technique lors de la suppression de l'utilisateur.");
        }
    }
}
