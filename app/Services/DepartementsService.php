<?php

namespace App\Services;

use App\DTO\DepartementDTO;
use App\Models\Departement;
use App\Services\Interface\DepartementsServiceInterface;

class DepartementsService implements Interface\DepartementsServiceInterface
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
}
