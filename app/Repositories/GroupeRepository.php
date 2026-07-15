<?php

namespace App\Repositories;

use App\Exception\{GroupeNotFoundException, PersistenceException, UserNotFoundException};
use App\Models\Groupe;
use App\Repositories\Interfaces\GroupeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class GroupeRepository implements GroupeRepositoryInterface
{
    // --- LECTURE ---

    /**
     * @inheritDoc
     */
    public function readAll(): Collection
    {
        return Groupe::with("children:id,parent_id")->get();
    }

    /**
     * @inheritDoc
     */
    public function read(int $id): Groupe
    {
        $groupe = Groupe::where("id", $id)->with("children:id,parent_id")->first();

        if (!$groupe) {
            throw new GroupeNotFoundException("Le groupe avec l'ID $id est introuvable.");
        }

        return $groupe;
    }

    // --- ÉCRITURE (CRUD) ---

    /**
     * @inheritDoc
     */
    public function create(array $data): Groupe
    {
        try {
            $groupe = new Groupe();
            $groupe->name = $data['name'];
            $groupe->initials = $data['initials'];

            if (isset($data['color'])) {
                $groupe->color = $data['color'];
            }

            if (!empty($data['parent'])) {
                $parentGroup = Groupe::find($data['parent']);
                // Sécurité : Le parent ne doit pas déjà avoir de parent
                if ($parentGroup && $parentGroup->parent_id !== null) {
                    throw new \InvalidArgumentException("Le parent sélectionné est déjà un sous-groupe (profondeur max: 2).");
                }
                $groupe->parent_id = $data['parent'];
            }

            $groupe->save();
            return $groupe;
        } catch (\InvalidArgumentException $e) {
            throw $e; // On laisse remonter l'erreur de validation métier
        } catch (Throwable $t) {
            Log::error("Erreur SQL lors de la création d'un groupe", [
                'data' => $data,
                'message' => $t->getMessage()
            ]);
            throw new PersistenceException("Impossible de créer le groupe.");
        }
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $data): Groupe
    {
        $groupe = $this->read($id);

        try {
            if (isset($data['name'])) $groupe->name = $data['name'];
            if (isset($data['initials'])) $groupe->initials = $data['initials'];
            if (isset($data['color'])) $groupe->color = $data['color'];

            // array_key_exists permet de capter la valeur 'null' pour retirer un parent
            if (array_key_exists('parent', $data)) {
                if ($data['parent'] !== null) {
                    $parentGroup = Groupe::find($data['parent']);

                    // Sécurité 1 : Le nouveau parent ne doit pas déjà avoir de parent
                    if ($parentGroup && $parentGroup->parent_id !== null) {
                        throw new \InvalidArgumentException("Le groupe parent sélectionné est déjà un sous-groupe (profondeur max: 2).");
                    }

                    // Sécurité 2 : Le groupe actuel ne doit pas déjà avoir d'enfants
                    if ($groupe->children()->count() > 0) {
                        throw new \InvalidArgumentException("Ce groupe possède déjà des sous-groupes. Il ne peut pas devenir enfant d'un autre groupe.");
                    }

                    $groupe->parent_id = $data['parent'];
                } else {
                    $groupe->parent_id = null;
                }
            }

            $groupe->save();
            return $groupe;
        } catch (\InvalidArgumentException $e) {
            throw $e; // On laisse remonter l'erreur de validation métier
        } catch (Throwable $t) {
            Log::error("Erreur SQL lors de la mise à jour du groupe $id", [
                'data' => $data,
                'message' => $t->getMessage()
            ]);
            throw new PersistenceException("Erreur lors de la mise à jour du groupe.");
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): void
    {
        $groupe = $this->read($id);

        try {
            $groupe->delete();
        } catch (Throwable $t) {
            Log::error("Échec de suppression du groupe $id", ['message' => $t->getMessage()]);
            throw new PersistenceException("Le groupe ne peut pas être supprimé actuellement.");
        }
    }

    // --- RELATIONS ---

    /**
     * @inheritDoc
     */
    public function readWithUsers(int $id): Groupe
    {
        return Groupe::where("id", $id)->with([
            'users' => fn ($q) => $q->with("groupes:id"),
        ])->first();
    }

    /**
     * @inheritDoc
     */
    public function removeUser(string|int $id, string|int $user_id): void
    {
        $groupe = $this->read($id);

        // Vérification de l'existence de la liaison pivot
        $userExists = $groupe->users()->where('user_id', $user_id)->exists();

        if (!$userExists) {
            throw new UserNotFoundException("L'utilisateur $user_id n'existe pas dans le groupe $id.");
        }

        try {
            $groupe->users()->detach($user_id);
        } catch (Throwable $t) {
            Log::error("Erreur SQL lors du détachement de l'utilisateur", [
                'grp_id' => $id,
                'user_id' => $user_id,
                'message' => $t->getMessage()
            ]);
            throw new PersistenceException("Impossible de détacher l'utilisateur du groupe.");
        }
    }
}
