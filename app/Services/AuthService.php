<?php

namespace App\Services;

use App\repositories\interfaces\UserRepositoryInterface;
use App\Services\Interface\DecodageServiceInterface;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Client\ConnectionException;

class AuthService implements Interface\UserServiceInterface {
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private DecodageServiceInterface $decodageService,
        private string $lang = "fr"
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
}
