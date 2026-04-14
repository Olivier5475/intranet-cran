<?php

namespace App\Repositories;

use App\Exception\{DepartementNotFoundException, PersistenceException, UserNotFoundException};
use App\Models\Departement;
use App\Repositories\Interfaces\DepartementRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class DepartementRepository implements DepartementRepositoryInterface
{
    // --- LECTURE ---

    /**
     * @inheritDoc
     */
    public function readAll(): Collection
    {
        return Departement::all();
    }

    /**
     * @inheritDoc
     */
    public function read(int $id): Departement
    {
        $departement = Departement::find($id);

        if (!$departement) {
            throw new DepartementNotFoundException("Le département avec l'ID $id est introuvable.");
        }

        return $departement;
    }

    // --- ÉCRITURE (CRUD) ---

    /**
     * @inheritDoc
     */
    public function create(array $data): Departement
    {
        try {
            $departement = new Departement();
            $departement->name = $data['name'];
            $departement->initials = $data['initials'];

            if (isset($data['color'])) {
                $departement->color = $data['color'];
            }

            $departement->save();
            return $departement;
        } catch (Throwable $t) {
            Log::error("Erreur SQL lors de la création d'un département", [
                'data' => $data,
                'message' => $t->getMessage()
            ]);
            throw new PersistenceException("Impossible de créer le département.");
        }
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $data): Departement
    {
        $departement = $this->read($id);

        try {
            if (isset($data['name'])) $departement->name = $data['name'];
            if (isset($data['initials'])) $departement->initials = $data['initials'];
            if (isset($data['color'])) $departement->color = $data['color'];

            $departement->save();
            return $departement;
        } catch (Throwable $t) {
            Log::error("Erreur SQL lors de la mise à jour du département $id", [
                'data' => $data,
                'message' => $t->getMessage()
            ]);
            throw new PersistenceException("Erreur lors de la mise à jour du département.");
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): void
    {
        $departement = $this->read($id);

        try {
            $departement->delete();
        } catch (Throwable $t) {
            Log::error("Échec de suppression du département $id", ['message' => $t->getMessage()]);
            throw new PersistenceException("Le département ne peut pas être supprimé actuellement.");
        }
    }

    // --- RELATIONS ---

    /**
     * @inheritDoc
     */
    public function readUsers(int $id): Collection
    {
        return $this->read($id)->users;
    }

    /**
     * @inheritDoc
     */
    public function removeUser(string|int $id, string|int $user_id): void
    {
        $departement = $this->read($id);

        // Vérification de l'existence de la liaison pivot
        $userExists = $departement->users()->where('user_id', $user_id)->exists();

        if (!$userExists) {
            throw new UserNotFoundException("L'utilisateur $user_id n'existe pas dans le département $id.");
        }

        try {
            $departement->users()->detach($user_id);
        } catch (Throwable $t) {
            Log::error("Erreur SQL lors du détachement de l'utilisateur", [
                'dept_id' => $id,
                'user_id' => $user_id,
                'message' => $t->getMessage()
            ]);
            throw new PersistenceException("Impossible de détacher l'utilisateur du département.");
        }
    }
}
