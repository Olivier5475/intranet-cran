<?php

namespace App\Services;

use App\DTO\GroupeDTO;
use App\Exception\{GroupeNotFoundException, PersistenceException, UserNotFoundException};
use App\Repositories\Interfaces\GroupeRepositoryInterface;
use App\Services\Interfaces\{GroupesServiceInterface, MapDTOServiceInterface};
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

readonly class GroupesService implements GroupesServiceInterface
{
    public function __construct(
        private GroupeRepositoryInterface $groupeRepository,
        private MapDTOServiceInterface    $mapDTOService,
    ) {}

    // --- LECTURE & CONSULTATION ---

    /**
     * @inheritDoc
     */
    public function readAll(): Collection
    {
        $groupes = $this->groupeRepository->readAll();
        return $this->mapDTOService->mapToGroupeDTOsCollection($groupes);
    }

    /**
     * @inheritDoc
     */
    public function readById(int $id): GroupeDTO
    {
        try {
            $groupe = $this->groupeRepository->readWithUsers($id);
            return $this->mapDTOService->mapToGroupeDTO($groupe);
        } catch (GroupeNotFoundException $e) {
            Log::warning("Consultation d'un groupe inexistant", ["id" => $id]);
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
            $this->groupeRepository->create($data);
            Log::info("Nouveau groupe créé", ["initials" => $data["initials"]]);
        } catch (PersistenceException $e) {
            Log::error("Échec de création de groupe", [
                "data" => $data,
                "error" => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $data): GroupeDTO
    {
        try {
            $groupe = $this->groupeRepository->update($id, $data);
            Log::info("Groupe mis à jour", ["id" => $id]);
            return $this->mapDTOService->mapToGroupeDTO($groupe);
        } catch (PersistenceException | GroupeNotFoundException $e) {
            Log::error("Échec de la mise à jour du groupe", [
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
            $this->groupeRepository->delete($id);
            Log::info("Groupe supprimé", ["id" => $id]);
        } catch (PersistenceException | GroupeNotFoundException $e) {
            Log::error("Erreur lors de la suppression du groupe", [
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
            $users = $this->groupeRepository->readUsers($id);
            return $this->mapDTOService->mapToAuthDTOsCollection($users);
        } catch (GroupeNotFoundException $e) {
            Log::alert("Tentative d'accès aux utilisateurs d'un groupe inexistant", [
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
            $this->groupeRepository->removeUser($id, $user_id);
            Log::info("Utilisateur retiré du groupe", ["dept_id" => $id, "user_id" => $user_id]);
        } catch (PersistenceException | GroupeNotFoundException | UserNotFoundException $e) {
            Log::error("Erreur lors du retrait de l'utilisateur du groupe", [
                "erreur" => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
