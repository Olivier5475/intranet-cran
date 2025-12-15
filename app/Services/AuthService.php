<?php

namespace App\Services;

use App\DTO\AuthDTO;
use App\Exception\PersistenceException;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

readonly class AuthService implements Interfaces\UserServiceInterface {
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    public function handleUserInDatabase(array $data): void {
        $user = $this->userRepository->getUserByEmail($data['email']);

        if (!$user) {
            $data["verified_member_role"] = true;
            try {
                $this->userRepository->createUser($data);
            } catch (PersistenceException $e) {
                Log::error("Erreur lors de la persistance des données", ["erreur" => $e]);
                throw $e;
            }
        }

    }

    public function getUserByEmail(string $string) : ?Authenticatable {
        return $this->userRepository->getUserByEmail($string);
    }

    public function getCurrentUserId() : int {
        return Auth::id();
    }

    public function readAll() : array {
        $users = $this->userRepository->readAll();
        $res = [];
        foreach ($users as $user) {
            $res[] = new AuthDTO(
                email: $user->email,
                nom: $user->nom,
                prenom: $user->prenom,
                role: $user->role,
                id : $user->id
            );
        }
        return $res;
    }

    public function delete(int $id) {
        try {
            $this->userRepository->delete($id);
        } catch (PersistenceException $e) {
            Log::error($e->getMessage());
            throw $e;
        }
    }

    public function readById($id) : AuthDTO {
        $user = $this->userRepository->getUserById($id);
        return new AuthDTO(
            email: $user->email,
            nom: $user->nom,
            prenom: $user->prenom,
            role: $user->role,
            id: $user->id,
        );
    }

    public function update(mixed $id, array $data) {

    }
}
