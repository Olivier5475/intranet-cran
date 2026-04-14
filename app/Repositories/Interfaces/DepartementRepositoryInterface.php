<?php

namespace App\Repositories\Interfaces;

use App\Models\Departement;
use Illuminate\Database\Eloquent\Collection;
use App\Exception\{DepartementNotFoundException, PersistenceException, UserNotFoundException};

interface DepartementRepositoryInterface
{
    /**
     * Récupère tous les départements enregistrés.
     *
     * @return Collection<int, Departement>
     */
    public function readAll(): Collection;

    /**
     * Récupère un département par son identifiant unique.
     *
     * @param int $id
     * @return Departement
     * @throws DepartementNotFoundException Si le département n'existe pas.
     */
    public function read(int $id): Departement;

    /**
     * Crée un nouveau département.
     *
     * @param array{name: string, initials: string, color?: string} $data
     * @return Departement
     * @throws PersistenceException Si l'enregistrement échoue.
     */
    public function create(array $data): Departement;

    /**
     * Met à jour les informations d'un département.
     *
     * @param int $id
     * @param array $data
     * @return Departement
     * @throws DepartementNotFoundException
     * @throws PersistenceException
     */
    public function update(int $id, array $data): Departement;

    /**
     * Supprime définitivement un département de la base.
     *
     * @param int $id
     * @return void
     * @throws DepartementNotFoundException
     * @throws PersistenceException
     */
    public function delete(int $id): void;

    /**
     * Récupère la collection des utilisateurs rattachés au département.
     *
     * @param int $id
     * @return Collection
     * @throws DepartementNotFoundException
     */
    public function readUsers(int $id): Collection;

    /**
     * Retire un utilisateur de la relation pivot du département.
     *
     * @param string|int $id ID du département.
     * @param string|int $user_id ID de l'utilisateur.
     * @return void
     * @throws UserNotFoundException Si l'utilisateur n'est pas lié à ce département.
     * @throws PersistenceException Si le détachement échoue.
     */
    public function removeUser(string|int $id, string|int $user_id): void;
}
