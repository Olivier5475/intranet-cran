<?php

namespace App\Services;

use App\DTO\AuthDTO;
use App\Exception\PersistenceException;
use App\Exception\UserNotFoundException;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\Interfaces\DepartementsServiceInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use phpCAS;

readonly class AuthService implements Interfaces\UserServiceInterface {
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private DepartementsServiceInterface $departementsService
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
                departements: $this->departementsService->departementsIDs($user->departements),
                role: $user->role,
                id: $user->id
            );
        }
        return $res;
    }

    public function delete(int $id): void
    {
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
            departements: $this->departementsService->departementsIDs($user->departements),
            role: $user->role,
            id: $user->id
        );
    }

    public function update(int $id, array $data) : void {
        try {
            $this->userRepository->updateUser($id, $data);
        } catch(PersistenceException|UserNotFoundException $e) {
            Log::error($e->getMessage());
            throw $e;
        }
    }

    public function getRole() : string {
        $user_id = $this->getCurrentUserId();
        $user = $this->userRepository->getUserById($user_id);
        return $user->role;
    }
    public function isAdmin() : bool {
        return $this->getRole() === "admin";
    }

    public function logout() : void {
        phpCAS::logout();
    }
}
