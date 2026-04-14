<?php

namespace App\Services\Interfaces;

use App\DTO\DepartementDTO;
use App\Exception\{DepartementNotFoundException, PersistenceException, UserNotFoundException};
use Illuminate\Support\Collection;

interface DepartementsServiceInterface
{
    /**
     * Récupère la liste de tous les départements.
     *
     * @return Collection<int, DepartementDTO> Collection de DTOs de départements.
     */
    public function readAll(): Collection;

    /**
     * Récupère un département spécifique par son identifiant.
     *
     * @param int $id Identifiant du département.
     * @return DepartementDTO
     * @throws DepartementNotFoundException Si le département n'existe pas.
     */
    public function readById(int $id): DepartementDTO;

    /**
     * Récupère la liste des utilisateurs rattachés à un département.
     *
     * @param int $id Identifiant du département.
     * @return Collection<int, \App\DTO\AuthDTO> Collection de DTOs d'utilisateurs.
     * @throws DepartementNotFoundException Si le département n'existe pas.
     */
    public function getUsers(int $id): Collection;

    /**
     * Crée un nouveau département.
     *
     * @param array{name: string, initials: string, color: string} $data
     * @return void
     * @throws PersistenceException Si l'enregistrement en base échoue.
     */
    public function create(array $data): void;

    /**
     * Met à jour les informations d'un département.
     *
     * @param int $id Identifiant du département.
     * @param array $data Données à modifier.
     * @return DepartementDTO Le DTO mis à jour.
     * @throws DepartementNotFoundException
     * @throws PersistenceException
     */
    public function update(int $id, array $data): DepartementDTO;

    /**
     * Supprime définitivement un département.
     *
     * @param int $id Identifiant du département.
     * @return void
     * @throws DepartementNotFoundException
     * @throws PersistenceException
     */
    public function delete(int $id): void;

    /**
     * Retire un utilisateur d'un département (suppression du lien pivot).
     *
     * @param string $id Identifiant du département.
     * @param string $user_id Identifiant de l'utilisateur.
     * @return void
     * @throws DepartementNotFoundException
     * @throws UserNotFoundException
     * @throws PersistenceException
     */
    public function removeUser(string $id, string $user_id): void;
}
