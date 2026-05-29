<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use App\Exception\{FavoriNotFoundException, PersistenceException, UserNotFoundException};
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

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
     * Récupère la liste des utilisateurs ayant un ID qui n'est pas dans la liste
     * * @param array<int> $usersIds
     * @return Collection<User>
     */
    public function getExcludeUsers(array $usersIds) : Collection;

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
     * * @param array{email: string, nom: string, prenom: string, role?: string, groupes?: array} $data
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

    /**
     * Ajoute une ressource aux favoris de l'utilisateur connecté.
     *
     * @param int $ressource_id L'identifiant unique de la ressource à mettre en favori.
     * @param string $ressource_type Le type de la ressource (ex: 'document', 'folder', 'file').
     * @return void
     * * @throws UserNotFoundException Si l'utilisateur n'est pas authentifié.
     * @throws BadRequestException Si le type de ressource n'est pas reconnu.
     * @throws ResourceNotFoundException Si la ressource ciblée n'existe pas en base de données.
     */
    public function addFavorites(int $ressource_id, string $ressource_type): void;

    /**
     * Supprime un favori spécifique appartenant à l'utilisateur connecté.
     *
     * @param string $ressource_type
     * @param int $ressource_id
     * @return void
     * * @throws FavoriNotFoundException Si le favori n'existe pas ou a déjà été supprimé.
     * @throws FavoriNotFoundException Si le favori n'existe pas ou a déjà été supprimé.
     * @throws AccessDeniedException Si l'utilisateur tente de supprimer le favori d'un autre.
     */
    public function removeFavorites(string $ressource_type, int $ressource_id): void;

    /**
     * Récupère la liste de tous les favoris de l'utilisateur connecté.
     * Les données retournées incluent les relations (Eager Loading) vers les ressources associées.
     *
     * @return Collection Une collection d'objets favoris.
     */
    public function getFavorites(): Collection;
}
