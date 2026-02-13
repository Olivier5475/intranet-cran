<?php

namespace App\Repositories;

use App\Exception\DepartementNotFoundException;
use App\Exception\PersistenceException;
use App\Models\Departement;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class DepartementRepository implements Interfaces\DepartementRepositoryInterface {

    public function create(array $data): Departement {
        try {
            // Utilisation de create() pour plus de concision (vérifie le $fillable dans le modèle)
            return Departement::create([
                'initials' => $data['initials'],
                'name' => $data['name'],
            ]);
        } catch (Throwable $t) {
            Log::error("Erreur SQL lors de la création d'un département", [
                'data' => $data,
                'message' => $t->getMessage()
            ]);
            throw new PersistenceException("Impossible de créer le département.");
        }
    }

    public function update(int $id, array $data): void {
        $departement = $this->getById($id);

        try {
            $departement->update([
                'initials' => $data['initials'] ?? $departement->initials,
                'name' => $data['name'] ?? $departement->name,
            ]);
        } catch (Throwable $t) {
            Log::error("Erreur SQL lors de la mise à jour du département $id", [
                'data' => $data,
                'message' => $t->getMessage()
            ]);
            throw new PersistenceException("Erreur lors de la mise à jour des données du département.");
        }
    }

    public function readAll(): Collection {
        return Departement::all();
    }

    public function getById(int $id) : Departement {
        $departement = Departement::find($id);

        if (!$departement) {
            throw new DepartementNotFoundException("Le département avec l'ID $id est introuvable.");
        }

        return $departement;
    }

    public function delete(int $id) : void {
        $departement = $this->getById($id); // Garantit que l'ID existe avant de tenter la suppression

        try {
            $departement->delete();
        } catch (Throwable $t) {
            Log::error("Échec de suppression du département $id", [
                'message' => $t->getMessage()
            ]);
            throw new PersistenceException("Le département ne peut pas être supprimé actuellement.");
        }
    }
}
