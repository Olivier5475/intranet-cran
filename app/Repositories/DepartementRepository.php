<?php

namespace App\Repositories;

use App\Exception\DepartementNotFoundException;
use App\Exception\PersistenceException;
use App\Exception\UserNotFoundException;
use App\Models\Departement;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class DepartementRepository implements Interfaces\DepartementRepositoryInterface {

    /**
     * @throws \Exception
     */
    public function create(array $data): Departement {
        try {
            $departement = new Departement();
            $departement->initials = $data['initials'];
            $departement->name = $data['name'];
            $departement->save();

            return $departement;
        } catch (\Exception) {
            throw new PersistenceException("Error creating departement");
        }
    }

    public function update(int $id, array $data): void {
        $departement = $this->getById($id);

        try {
            $departement->initials = $data['initials'];
            $departement->name = $data['name'];
            $departement->save();
        } catch (\Throwable) {
            throw new PersistenceException("Erreur lors de la mise à jour de l'utilisateur.");
        }
    }

    public function readAll(): Collection {
        return Departement::all();
    }

    public function getById(int $id) : Departement {
        $departement = Departement::find($id);
        if (empty($departement)) {
            throw new DepartementNotFoundException();
        }
        return $departement;
    }

    public function delete(int $id) : void {
        try {
            $user = Departement::find($id);
            $user->delete();
        } catch (\Throwable) {
           throw new PersistenceException();
        }
    }
}
