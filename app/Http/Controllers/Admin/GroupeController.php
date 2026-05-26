<?php

namespace App\Repositories; // Attention, vérifie ton namespace, c'était Controllers avant

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveGroupeRequest;
use App\Services\Interfaces\GroupesServiceInterface;
use App\Services\Interfaces\UserServiceInterface;
use App\Exception\{GroupeNotFoundException, PersistenceException, UserNotFoundException};
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Throwable;

class GroupeController extends Controller {

    public function __construct(
        private readonly GroupesServiceInterface $groupesService,
        private readonly UserServiceInterface    $usersService,
    ){}

    // --- VUES ---

    public function index() {
        // Liste déjà partagée par HandleInertiaRequests
        return Inertia::render("Admin/Groupes");
    }

    public function users(int $id)
    {
        try {
            $groupe = $this->groupesService->readById($id);
            $users = $groupe->users;
            $usersIds = $users->pluck('id')->toArray();

            // On ne récupère que les utilisateurs qui ne sont PAS dans le groupe
            $otherUsers = $this->usersService->getUsersWhereNotIn($usersIds);
            return Inertia::render("Admin/UsersByGroupe", [
                "groupe" => $groupe,
                "othersUsers" => $otherUsers->values(), // values() pour réindexer proprement le tableau
            ]);
        } catch (Throwable $t) {
            return $this->handleException($t, "chargement des utilisateurs", $id);
        }
    }

    // --- ACTIONS ---

    public function store(SaveGroupeRequest $request) {
        try {
            $this->groupesService->create($request->validated());
            return redirect()->route("admin.groupes")->with("success", "Groupe créé avec succès.");
        } catch (Throwable $t) {
            return $this->handleException($t, "création");
        }
    }

    public function update(SaveGroupeRequest $request, int $id) {
        try {
            $this->groupesService->update($id, $request->validated());
            return redirect()->route("admin.groupes")->with("success", "Groupe mis à jour avec succès.");
        } catch (Throwable $t) {
            return $this->handleException($t, "mise à jour", $id);
        }
    }

    public function delete(int $id) {
        try {
            $this->groupesService->delete($id);
            return redirect()->back()->with("success", "Groupe supprimé avec succès.");
        } catch (Throwable $t) {
            return $this->handleException($t, "suppression", $id);
        }
    }

    public function removeUser(int $id, int $user_id) {
        try {
            $this->groupesService->removeUser($id, $user_id);
            return redirect()->back()->with("success", "Utilisateur retiré du groupe.");
        } catch (Throwable $t) {
            return $this->handleException($t, "retrait de l'utilisateur", $id);
        }
    }

    // --- GESTION DES ERREURS ---

    private function handleException(Throwable $t, string $action, int $id = null) {
        Log::error("Erreur Groupe $action", [
            'id' => $id,
            'message' => $t->getMessage()
        ]);

        $message = match(get_class($t)) {
            GroupeNotFoundException::class => "Groupe introuvable.",
            UserNotFoundException::class        => "Utilisateur introuvable.",
            PersistenceException::class         => "Action impossible pour le moment (contrainte technique).",
            default                             => "Une erreur imprévue est survenue lors de la $action."
        };

        return redirect()->back()->with('error', $message);
    }
}
