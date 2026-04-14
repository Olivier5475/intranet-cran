<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveUserRequest;
use App\Services\Interfaces\UserServiceInterface;
use App\Exception\PersistenceException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\UnauthorizedException;
use Inertia\Inertia;
use Throwable;

class UsersController extends Controller {

    public function __construct(
        private readonly UserServiceInterface $usersService,
    ){}

    // --- VUES ---

    public function index(Request $request) {
        try {
            $searchQuery = $request->input('q');
            return Inertia::render("Admin/Users", [
                "users" => $this->usersService->getUsers($searchQuery)
            ]);
        } catch (Throwable $t) {
            return $this->handleException($t, "chargement");
        }
    }

    // --- ACTIONS ---

    public function store(SaveUserRequest $request) {
        try {
            // handleUserInDatabase vérifie déjà l'existence et l'annuaire 12Plus
            $this->usersService->handleUserInDatabase($request->validated());

            return redirect()->route("admin.user")->with("success", "Utilisateur créé avec succès.");
        } catch (UnauthorizedException) {
            return redirect()->back()->with("error", "Cet utilisateur ne fait pas partie de l'annuaire 12Plus.");
        } catch (Throwable $t) {
            return $this->handleException($t, "création");
        }
    }

    public function update(SaveUserRequest $request, int $id) {
        try {
            $this->usersService->update($id, $request->validated());

            return redirect()->route("admin.user")->with("success", "Utilisateur mis à jour avec succès.");
        } catch (Throwable $t) {
            return $this->handleException($t, "mise à jour", $id);
        }
    }

    public function delete(int $id) {
        try {
            $this->usersService->delete($id);
            return redirect()->back()->with("success", "Utilisateur supprimé avec succès.");
        } catch (Throwable $t) {
            return $this->handleException($t, "suppression", $id);
        }
    }

    // --- HELPER D'ERREURS ---

    private function handleException(Throwable $t, string $action, int $id = null) {
        Log::error("Erreur Utilisateur $action", [
            'id' => $id,
            'error' => $t->getMessage()
        ]);

        $message = match(get_class($t)) {
            PersistenceException::class => "Erreur technique lors de la sauvegarde en base de données.",
            default => "Une erreur imprévue est survenue lors de la $action de l'utilisateur."
        };

        return redirect()->back()->with('error', $message);
    }
}
