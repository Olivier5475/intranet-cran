<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use App\Exception\{PersistenceException, UserNotFoundException};

interface UserRepositoryInterface
{
    /**
     * Récupère un utilisateur par son adresse email (clé unique CAS).
     * * @param string $email
     * @return User|null
     */
    public function getUserByEmail(string $email): ?User;

    /**
     * Récupère un utilisateur par son identifiant unique.
     * * @param int $id
     * @return User
     * @throws UserNotFoundException
     */
    public function getUserById(int $id): User;

    /**
     * Récupère la liste de tous les utilisateurs.
     * * @return Collection<int, User>
     */
    public function readAll(): Collection;

    /**
     * Recherche des utilisateurs via Meilisearch/Scout (nom, prénom, email).
     * * @param string $query
     * @return Collection<int, User>
     */
    public function performSearch(string $query): Collection;

    /**
     * Crée un nouvel utilisateur en base de données.
     * * @param array{email: string, nom: string, prenom: string, role?: string, departements?: array} $data
     * @return User
     * @throws PersistenceException
     */
    public function createUser(array $data): User;

    /**
     * Met à jour les informations d'un utilisateur.
     * * @param int $id
     * @param array $data
     * @return void
     * @throws UserNotFoundException
     * @throws PersistenceException
     */
    public function update(int $id, array $data): void;

    /**
     * Supprime définitivement un utilisateur.
     * * @param int $id
     * @return void
     * @throws UserNotFoundException
     * @throws PersistenceException
     */
    public function delete(int $id): void;
}
