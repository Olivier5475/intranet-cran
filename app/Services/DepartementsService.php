<?php

namespace App\Services;

use App\DTO\AuthDTO;
use App\DTO\DepartementDTO;
use App\Exception\DepartementNotFoundException;
use App\Exception\PersistenceException;
use App\Exception\UserNotFoundException;
use App\Repositories\Interfaces\DepartementRepositoryInterface;
use App\Services\Interfaces\DepartementsServiceInterface;
use App\Services\Interfaces\MapDTOServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Throwable;

readonly class DepartementsService implements DepartementsServiceInterface
{
    public function __construct(
        private DepartementRepositoryInterface $departementRepository,
        private MapDTOServiceInterface         $mapDTOService,
    ) {}

    public function create(array $data): void
    {
        try {
            $this->departementRepository->create($data);
            Log::info("Nouveau département créé", ["initials" => $data["initials"]]);
        } catch (PersistenceException $e) {
            Log::error("Échec de création de département", [
                "data" => $data,
                "error" => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function readAll(): Collection
    {
        $departements = $this->departementRepository->readAll();
        return $this->mapDTOService->mapToDepartementDTOsCollection($departements);
    }

    public function delete(int $id): void
    {
        try {
            $this->departementRepository->delete($id);
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
            // On récupère le departement
            $departement = $this->departementRepository->getById($id);

            // On le renvoie sous forme de DTO
            return $this->mapDTOService->mapToDepartementDTO($departement);
        } catch (DepartementNotFoundException $e) {
            Log::warning("Consultation d'un département inexistant", ["id" => $id]);
            throw $e;
        }
    }

    public function update(int $id, array $data): DepartementDTO
    {
        try {
            $departement = $this->departementRepository->update($id, $data);
            Log::info("Département mis à jour", ["id" => $id]);
            return $this->mapDTOService->mapToDepartementDTO($departement);
        } catch (PersistenceException | DepartementNotFoundException $e) {
            Log::error("Échec de la mise à jour du département", [
                "id" => $id,
                "data" => $data,
                "error" => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function getUsers($id): Collection
    {
        try {
            $users = $this->departementRepository->readUsers($id);
            return $this->mapDTOService->mapToAuthDTOsCollection($users);
        } catch (DepartementNotFoundException $e) {
            Log::alert("Attemp to access inexistant departement", [
                "erreur" => $e->getMessage(),
                "id" => $id
            ]);
            throw $e;
        }

    }

    public function removeUser(string $id, string $user_id): void
    {
        try {
            $this->departementRepository->removeUser($id, $user_id);
        } catch (PersistenceException | DepartementNotFoundException | UserNotFoundException $e) {
            Log::error("Erreur lors de la suppression du utilisateur", [
                "erreur" => $e->getMessage(),
            ]);
            throw $e ;
        }
    }
}
