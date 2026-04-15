<?php

namespace App\Repositories;

use App\Exception\{PersistenceException, UserNotFoundException};
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class UserRepository implements UserRepositoryInterface
{
    // --- LECTURE & AUTHENTIFICATION ---

    /**
     * @inheritDoc
     */
    public function getUserByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * @inheritDoc
     */
    public function getUserById(int $id): User
    {
        $user = User::find($id);
        if (!$user) {
            throw new UserNotFoundException("Utilisateur ID $id introuvable.");
        }
        return $user;
    }

    /**
     * @inheritDoc
     */
    public function readAll(): Collection
    {
        return User::all();
    }

    /**
     * @inheritDoc
     */
    public function getExcludeUsers(array $usersIds) : Collection
    {
        return User::whereNotIn('id', $usersIds)
            ->with("departements:id")
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function performSearch(string $query): Collection
    {
        return User::search($query)->get();
    }

    // --- ÉCRITURE (CRUD) ---

    /**
     * @inheritDoc
     */
    public function createUser(array $data): User
    {
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
            throw new PersistenceException("Erreur lors de la création de l'utilisateur.", 0, $t);
        }
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $data): void
    {
        $user = User::find($id);

        if (!$user) {
            throw new UserNotFoundException("Mise à jour impossible : utilisateur ID $id introuvable.");
        }

        try {
            if (isset($data['nom'])) $user->nom = $data['nom'];
            if (isset($data['prenom'])) $user->prenom = $data['prenom'];
            if (isset($data['email'])) $user->email = $data['email'];
            if (isset($data['role'])) $user->role = $data['role'];

            $user->save();

            if (isset($data['departements'])) {
                $user->departements()->sync($data['departements']);
            }
        } catch (Throwable $t) {
            Log::error("Erreur SQL lors de la mise à jour de l'utilisateur $id", [
                'message' => $t->getMessage(),
                'data' => $data
            ]);
            throw new PersistenceException("Erreur lors de la modification de l'utilisateur.");
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): void
    {
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
            throw new PersistenceException("Erreur technique lors de la suppression.");
        }
    }
}
