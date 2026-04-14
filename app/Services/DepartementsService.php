<?php

namespace App\Services;

use App\DTO\DepartementDTO;
use App\Exception\{DepartementNotFoundException, PersistenceException, UserNotFoundException};
use App\Repositories\Interfaces\DepartementRepositoryInterface;
use App\Services\Interfaces\{DepartementsServiceInterface, MapDTOServiceInterface};
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

readonly class DepartementsService implements DepartementsServiceInterface
{
    public function __construct(
        private DepartementRepositoryInterface $departementRepository,
        private MapDTOServiceInterface         $mapDTOService,
    ) {}

    // --- LECTURE & CONSULTATION ---

    /**
     * @inheritDoc
     */
    public function readAll(): Collection
    {
        $departements = $this->departementRepository->readAll();
        return $this->mapDTOService->mapToDepartementDTOsCollection($departements);
    }

    /**
     * @inheritDoc
     */
    public function readById(int $id): DepartementDTO
    {
        try {
            $departement = $this->departementRepository->read($id);
            return $this->mapDTOService->mapToDepartementDTO($departement);
        } catch (DepartementNotFoundException $e) {
            Log::warning("Consultation d'un département inexistant", ["id" => $id]);
            throw $e;
        }
    }

    // --- ÉCRITURE (CRUD) ---

    /**
     * @inheritDoc
     */
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

    /**
     * @inheritDoc
     */
    public function update(int $id, array $data): DepartementDTO
    {
        try {
            $departement = $this->departementRepository->update($id, $data);
            Log::info("Département mis à jour", ["id" => $id]);
            return $this->mapDTOService->mapToDepartementDTO($departement);
        } catch (PersistenceException | DepartementNotFoundException $e) {
            Log::error("Échec de la mise à jour du département", [
                "id" => $id,
                "error" => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): void
    {
        try {
            $this->departementRepository->delete($id);
            Log::info("Département supprimé", ["id" => $id]);
        } catch (PersistenceException | DepartementNotFoundException $e) {
            Log::error("Erreur lors de la suppression du département", [
                "id" => $id,
                "error" => $e->getMessage()
            ]);
            throw $e;
        }
    }

    // --- RELATIONS ---


    /**
     * @inheritDoc
     */
    public function getUsers($id): Collection
    {
        try {
            $users = $this->departementRepository->readUsers($id);
            return $this->mapDTOService->mapToAuthDTOsCollection($users);
        } catch (DepartementNotFoundException $e) {
            Log::alert("Tentative d'accès aux utilisateurs d'un département inexistant", [
                "erreur" => $e->getMessage(),
                "id" => $id
            ]);
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function removeUser(string $id, string $user_id): void
    {
        try {
            $this->departementRepository->removeUser($id, $user_id);
            Log::info("Utilisateur retiré du département", ["dept_id" => $id, "user_id" => $user_id]);
        } catch (PersistenceException | DepartementNotFoundException | UserNotFoundException $e) {
            Log::error("Erreur lors du retrait de l'utilisateur du département", [
                "erreur" => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
