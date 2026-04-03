<?php

namespace App\Services;

use App\DTO\AuthDTO;
use App\DTO\DepartementDTO;
use App\Exception\DepartementNotFoundException;
use App\Exception\PersistenceException;
use App\Exception\UserNotFoundException;
use App\Repositories\Interfaces\DepartementRepositoryInterface;
use App\Services\Interfaces\DepartementsServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Throwable;

readonly class DepartementsService implements DepartementsServiceInterface
{
    public function __construct(
        private DepartementRepositoryInterface $repository
    ) {}

    public function create(array $data): void
    {
        try {
            $this->repository->create($data);
            Log::info("Nouveau département créé", ["initials" => $data["initials"]]);
        } catch (PersistenceException $e) {
            Log::error("Échec de création de département", [
                "data" => $data,
                "error" => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function readAll(): array
    {
        $departements = $this->repository->readAll();

        return array_map(fn($dept) => new DepartementDTO(
            id: $dept["id"],
            name: $dept["name"],
            initials: $dept["initials"],
        ), $departements->toArray());
    }

    public function departementsIDs(iterable $departements): array
    {
        $res = [];
        foreach ($departements as $departement) {
            $res[] = $departement->id;
        }
        return $res;
    }

    public function delete(int $id): void
    {
        try {
            $this->repository->delete($id);
            // On log l'information si ça a marché
            Log::info("Département supprimé", ["id" => $id]);
        } catch (PersistenceException | DepartementNotFoundException $e) {
            // On log l'erreur
            Log::error("Erreur lors de la suppression du département", [
                "id" => $id,
                "error" => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function readById(int $id): DepartementDTO
    {
        try {
            $departement = $this->repository->getById($id);

            return new DepartementDTO(
                id: $departement->id,
                name: $departement->name,
                initials: $departement->initials,
            );
        } catch (DepartementNotFoundException $e) {
            Log::warning("Consultation d'un département inexistant", ["id" => $id]);
            throw $e;
        }
    }

    public function update(int $id, array $data): DepartementDTO
    {
        try {
            $this->repository->update($id, $data);
            Log::info("Département mis à jour", ["id" => $id]);

            return $this->readById($id);
        } catch (PersistenceException | DepartementNotFoundException $e) {
            Log::error("Échec de la mise à jour du département", [
                "id" => $id,
                "data" => $data,
                "error" => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function getUsers($id): array
    {
        try {
            $users = $this->repository->readUsers($id);
        } catch (DepartementNotFoundException $e) {
            Log::alert("Attemp to access inexistant departement", [
                "erreur" => $e->getMessage(),
                "id" => $id
            ]);
            throw $e;
        }

        $usersDTOs = [];

        foreach ($users as $user) {
            $usersDTOs[] = new AuthDTO(
                email: $user->email,
                nom: $user->nom,
                prenom: $user->prenom,
                departements: $user->departements->pluck("id")->toArray(),
                role: $user->role,
                id: $user->id,
            );
        }

        return $usersDTOs;
    }

    public function removeUser(string $id, string $user_id): void
    {
        try {
            $this->repository->removeUser($id, $user_id);
        } catch (PersistenceException | DepartementNotFoundException | UserNotFoundException $e) {
            Log::error("Erreur lors de la suppression du utilisateur", [
                "erreur" => $e->getMessage(),
            ]);
            throw $e ;
        }
    }
}
