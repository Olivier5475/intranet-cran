<?php

namespace App\Services\Interfaces;

use App\DTO\AuthDTO;
use App\Exception\PersistenceException;
use App\Exception\UserNotFoundException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Validation\UnauthorizedException;

interface UserServiceInterface
{
    /**
     * Gère la connexion et la déconnexion via phpCAS.
     *
     * @param string $returnUrl URL de retour après la déconnexion.
     * @return void
     */
    public function logout(string $returnUrl): void;

    /**
     * Récupère l'identifiant de l'utilisateur actuellement connecté via Auth.
     *
     * @return int ID de l'utilisateur ou 0 si non connecté.
     */
    public function getCurrentUserId(): int;

    /**
     * Vérifie si l'utilisateur possède le rôle administrateur.
     *
     * @return bool
     */
    public function isAdmin(): bool;

    /**
     * Récupère le rôle de l'utilisateur actuel.
     *
     * @return string Le rôle (ex: 'admin', 'user') ou 'guest' par défaut.
     */
    public function getRole(): string;

    /**
     * Récupère une liste d'utilisateurs, éventuellement filtrée par une recherche.
     *
     * @param string|null $searchQuery Terme de recherche (nom, prénom, email).
     * @return Collection<int, AuthDTO> Collection de DTOs d'utilisateurs.
     * @throws \Throwable En cas d'erreur de mapping ou de base de données.
     */
    public function getUsers(?string $searchQuery = null): Collection;

    /**
     * Récupère les données complètes d'un utilisateur par son ID.
     *
     * @param int $id
     * @return AuthDTO
     * @throws UserNotFoundException Si l'utilisateur n'existe pas.
     */
    public function readById(int $id): AuthDTO;

    /**
     * Récupère le modèle utilisateur par son email (utilisé pour l'Auth Laravel).
     *
     * @param string $email
     * @return Authenticatable|null
     */
    public function getUserByEmail(string $email): ?Authenticatable;

    /**
     * Récupère tous les utilisateurs ayant un ID qui n'est pas dans la liste
     * et les transforme en DTOs
     *
     * @param array<int> $usersIds
     * @return Collection<AuthDTO>
     */
    public function getUsersWhereNotIn(array $usersIds) : Collection;

    /**
     * Assure la présence de l'utilisateur en base de données lors de la connexion CAS.
     * Si l'utilisateur n'existe pas, mais est autorisé (12Plus), il est créé.
     *
     * @param array{email: string, nom: string, prenom: string} $data
     * @return void
     * @throws UnauthorizedException Si l'email n'est pas autorisé par l'annuaire 12Plus.
     * @throws PersistenceException Si la création automatique échoue.
     */
    public function handleUserInDatabase(array $data): void;

    /**
     * Met à jour les informations d'un utilisateur.
     *
     * @param int $id
     * @param array $data Données à modifier (nom, rôle, départements, etc.).
     * @return void
     * @throws UserNotFoundException
     * @throws PersistenceException
     */
    public function update(int $id, array $data): void;

    /**
     * Supprime définitivement un utilisateur.
     *
     * @param int $id
     * @return void
     * @throws UserNotFoundException
     * @throws PersistenceException
     */
    public function delete(int $id): void;

    /**
     * Vérifie via un appel cURL si un email appartient à l'annuaire de l'intranet 12Plus.
     *
     * @param string $email
     * @return bool True si l'email est présent dans la liste.
     */
    public function emailExistIn12Plus(string $email): bool;
}
