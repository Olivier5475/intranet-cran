<?php

namespace App\Services\Interfaces;

use App\DTO\GroupeDTO;
use App\Exception\{GroupeNotFoundException, PersistenceException, UserNotFoundException};
use Illuminate\Support\Collection;

interface GroupesServiceInterface
{
    /**
     * Récupère la liste de tous les groupes.
     *
     * @return Collection<int, GroupeDTO> Collection de DTOs de groupes.
     */
    public function readAll(): Collection;

    /**
     * Récupère un groupe spécifique par son identifiant.
     *
     * @param int $id Identifiant du groupe.
     * @return GroupeDTO
     * @throws GroupeNotFoundException Si le groupe n'existe pas.
     */
    public function readById(int $id): GroupeDTO;

    /**
     * Récupère la liste des utilisateurs rattachés à un groupe.
     *
     * @param int $id Identifiant du groupe.
     * @return Collection<int, \App\DTO\AuthDTO> Collection de DTOs d'utilisateurs.
     * @throws GroupeNotFoundException Si le groupe n'existe pas.
     */
    public function getUsers(int $id): Collection;

    /**
     * Crée un nouveau groupe.
     *
     * @param array{name: string, initials: string, color: string} $data
     * @return void
     * @throws PersistenceException Si l'enregistrement en base échoue.
     */
    public function create(array $data): void;

    /**
     * Met à jour les informations d'un groupe.
     *
     * @param int $id Identifiant du groupe.
     * @param array $data Données à modifier.
     * @return GroupeDTO Le DTO mis à jour.
     * @throws GroupeNotFoundException
     * @throws PersistenceException
     */
    public function update(int $id, array $data): GroupeDTO;

    /**
     * Supprime définitivement un groupe.
     *
     * @param int $id Identifiant du groupe.
     * @return void
     * @throws GroupeNotFoundException
     * @throws PersistenceException
     */
    public function delete(int $id): void;

    /**
     * Retire un utilisateur d'un groupe (suppression du lien pivot).
     *
     * @param string $id Identifiant du groupe.
     * @param string $user_id Identifiant de l'utilisateur.
     * @return void
     * @throws GroupeNotFoundException
     * @throws UserNotFoundException
     * @throws PersistenceException
     */
    public function removeUser(string $id, string $user_id): void;
}
