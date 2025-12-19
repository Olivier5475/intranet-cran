<?php

namespace App\Services;

use App\DTO\DepartementDTO;
use App\Models\Departement;
use App\Services\Interfaces\DepartementsServiceInterface;

readonly class DepartementsService implements Interfaces\DepartementsServiceInterface
{

    public function getDepartements(): array {
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

    public function departementsIDs($departements): array {
        $res = [];
        foreach ($departements as $departement) {
            $res[] = $departement->id;
        }
        return $res;
    }
}
