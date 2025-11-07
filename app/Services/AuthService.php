<?php

namespace App\Services;

use App\Repositories\Interfaces\UserRepositoryInterface;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Auth;

readonly class AuthService implements Interfaces\UserServiceInterface {
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    /**
     * @throws ConnectionException
     * @throws Exception
     */
    public function handleUserInDatabase(array $data): void {
        $user = $this->userRepository->getUserByEmail($data['email']);

        if (!$user) {
            $data["verified_member_role"] = true;
            try {
                $this->userRepository->createUser($data);
            } catch (Exception $e) {
                throw new Exception("utilisateur non créer");
            }
        }

    }

    public function getUserByEmail(string $string) : ?Authenticatable {
        return $this->userRepository->getUserByEmail($string);
    }

    public function getCurrentUserId() : int {
        return Auth::id();
    }
}
