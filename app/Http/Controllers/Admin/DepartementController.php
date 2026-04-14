<?php

namespace App\Repositories; // Attention, vérifie ton namespace, c'était Controllers avant

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveDepartementRequest;
use App\Services\Interfaces\DepartementsServiceInterface;
use App\Exception\{DepartementNotFoundException, PersistenceException, UserNotFoundException};
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Throwable;

class DepartementController extends Controller {

    public function __construct(
        private readonly DepartementsServiceInterface $departementsService,
    ){}

    // --- VUES ---

    public function index() {
        // Liste déjà partagée par HandleInertiaRequests
        return Inertia::render("Admin/Departements");
    }

    public function users(int $id) {
        try {
            return Inertia::render("Admin/UsersByDepartement", [
                "users" => $this->departementsService->getUsers($id),
                "departement" => $this->departementsService->readById($id)
            ]);
        } catch (Throwable $t) {
            return $this->handleException($t, "chargement des utilisateurs", $id);
        }
    }

    // --- ACTIONS ---

    public function store(SaveDepartementRequest $request) {
        try {
            $this->departementsService->create($request->validated());
            return redirect()->route("admin.departements")->with("success", "Département créé avec succès.");
        } catch (Throwable $t) {
            return $this->handleException($t, "création");
        }
    }

    public function update(SaveDepartementRequest $request, int $id) {
        try {
            $this->departementsService->update($id, $request->validated());
            return redirect()->route("admin.departements")->with("success", "Département mis à jour avec succès.");
        } catch (Throwable $t) {
            return $this->handleException($t, "mise à jour", $id);
        }
    }

    public function delete(int $id) {
        try {
            $this->departementsService->delete($id);
            return redirect()->back()->with("success", "Département supprimé avec succès.");
        } catch (Throwable $t) {
            return $this->handleException($t, "suppression", $id);
        }
    }

    public function removeUser(int $id, int $user_id) {
        try {
            $this->departementsService->removeUser($id, $user_id);
            return redirect()->back()->with("success", "Utilisateur retiré du département.");
        } catch (Throwable $t) {
            return $this->handleException($t, "retrait de l'utilisateur", $id);
        }
    }

    // --- GESTION DES ERREURS ---

    private function handleException(Throwable $t, string $action, int $id = null) {
        Log::error("Erreur Département $action", [
            'id' => $id,
            'message' => $t->getMessage()
        ]);

        $message = match(get_class($t)) {
            DepartementNotFoundException::class => "Département introuvable.",
            UserNotFoundException::class        => "Utilisateur introuvable.",
            PersistenceException::class         => "Action impossible pour le moment (contrainte technique).",
            default                             => "Une erreur imprévue est survenue lors de la $action."
        };

        return redirect()->back()->with('error', $message);
    }
}
