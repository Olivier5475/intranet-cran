<?php

namespace App\Repositories\Interfaces;

use App\Models\Groupe;
use Illuminate\Database\Eloquent\Collection;
use App\Exception\{GroupeNotFoundException, PersistenceException, UserNotFoundException};

interface GroupeRepositoryInterface
{
    /**
     * Récupère tous les groupes enregistrés.
     *
     * @return Collection<int, Groupe>
     */
    public function readAll(): Collection;

    /**
     * Récupère un groupe par son identifiant unique.
     *
     * @param int $id
     * @return Groupe
     * @throws GroupeNotFoundException Si le groupe n'existe pas.
     */
    public function read(int $id): Groupe;

    /**
     * Crée un nouveau groupe.
     *
     * @param array{name: string, initials: string, color?: string} $data
     * @return Groupe
     * @throws PersistenceException Si l'enregistrement échoue.
     */
    public function create(array $data): Groupe;

    /**
     * Met à jour les informations d'un groupe.
     *
     * @param int $id
     * @param array $data
     * @return Groupe
     * @throws GroupeNotFoundException
     * @throws PersistenceException
     */
    public function update(int $id, array $data): Groupe;

    /**
     * Supprime définitivement un groupe de la base.
     *
     * @param int $id
     * @return void
     * @throws GroupeNotFoundException
     * @throws PersistenceException
     */
    public function delete(int $id): void;

    /**
     * Récupère un groupe selon un ID,
     * avec la collection des utilisateurs rattachés au groupe
     * et les Groupes relier à l'utilisateur
     *
     * @param int $id
     * @return Groupe
     * @throws GroupeNotFoundException
     */
    public function readWithUsers(int $id): Groupe;

    /**
     * Retire un utilisateur de la relation pivot du groupe.
     *
     * @param string|int $id ID du groupe.
     * @param string|int $user_id ID de l'utilisateur.
     * @return void
     * @throws UserNotFoundException Si l'utilisateur n'est pas lié à ce groupe.
     * @throws PersistenceException Si le détachement échoue.
     */
    public function removeUser(string|int $id, string|int $user_id): void;
}
