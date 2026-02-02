<?php

namespace App\Services;

use App\DTO\DepartementDTO;
use App\Exception\DepartementNotFoundException;
use App\Exception\PersistenceException;
use App\Models\Departement;
use App\Repositories\Interfaces\DepartementRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

readonly class DepartementsService implements Interfaces\DepartementsServiceInterface
{
    public function __construct(private DepartementRepositoryInterface $repository)
    {
    }

    public function create(array $data) : void
    {
        if(empty($data["initials"]) || empty($data["name"])) {
            throw new BadRequestException();
        }
        try {
            $this->repository->create($data);
        } catch (PersistenceException $e) {
            Log::error("Erreur lors de la persistance des données au moment de la creation d'un nouveau departement",
                ["erreur" => $e]);
            throw $e;
        }
    }

    public function readAll(): array
    {
        $departements = Departement::all();
        $dtos = [];
        foreach ($departements as $departement) {
            $dtos[] = new DepartementDTO(
                id: $departement->id,
                name: $departement->name,
                initials: $departement->initials,
            );
        }
        return $dtos;
    }

    public function departementsIDs($departements): array
    {
        $res = [];
        foreach ($departements as $departement) {
            $res[] = $departement->id;
        }
        return $res;
    }

    public function delete(int $id) : void
    {
        if(empty($id)) {
            throw new BadRequestException();
        }
        try {
            $this->repository->delete($id);
        } catch (PersistenceException $e) {
            Log::error($e->getMessage());
            throw $e;
        }
    }

    public function readById($id): DepartementDTO
    {
        if(empty($id)) {
            throw new BadRequestException();
        }
        $departement = $this->repository->getById($id);
        return new DepartementDTO(
            id: $departement->id,
            name: $departement->name,
            initials: $departement->initials,
        );
    }

    public function update(int $id, array $data): DepartementDTO
    {
        try {
            $this->repository->update($id, $data);
            return $this->readById($id);
        } catch (PersistenceException|DepartementNotFoundException $e) {
            Log::error($e->getMessage());
            throw $e;
        }
    }
}
