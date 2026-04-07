<?php

namespace App\Services\Interfaces;

use App\DTO\AuthDTO;
use App\Exception\PersistenceException;
use App\Exception\UserNotFoundException;
use Illuminate\Contracts\Auth\Authenticatable;
use Throwable;

interface UserServiceInterface {

    /**
     * Vérifie si l'utilisateur existe, sinon le crée.
     * @param array $data
     * @throws PersistenceException
     */
    public function handleUserInDatabase(array $data): void;

    public function emailExistIn12Plus(string $email): bool;
    /**
     * @param string $email
     * @return Authenticatable|null
     */
    public function getUserByEmail(string $email): ?Authenticatable;

    /**
     * @return int
     */
    public function getCurrentUserId(): int;

    /**
     * @param int $id
     * @param array $data
     * @throws UserNotFoundException
     * @throws PersistenceException
     */
    public function update(int $id, array $data): void;

    /**
     * @param int $id
     * @return AuthDTO
     * @throws UserNotFoundException
     */
    public function readById(int $id): AuthDTO;

    /**
     * @param int $id
     * @throws PersistenceException
     * @throws UserNotFoundException
     */
    public function delete(int $id): void;


    /**
     * @param ?string $searchQuery
     * @return AuthDTO[]
     * @throws Throwable
     */
    public function getUsers(?string $searchQuery): array;

    /**
     * @return string
     */
    public function getRole(): string;

    /**
     * @return bool
     */
    public function isAdmin(): bool;

    /**
     * Déconnecte l'utilisateur via CAS.
     * @param string $returnUrl
     */
    public function logout(string $returnUrl): void;
}
