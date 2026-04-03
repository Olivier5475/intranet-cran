<?php

namespace App\Http\Controllers\Admin;

use App\Exception\PersistenceException;
use App\Http\Controllers\Controller;
use App\Services\Interfaces\UserServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\UnauthorizedException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Throwable;
use Inertia\Inertia;

class UsersController extends Controller {
    public function __construct(
        private readonly UserServiceInterface $usersService,
    ){}

    public function readAll(Request $request) {
        $searchQuery = $request->input('q');

        try {
            return Inertia::render("Admin/Users", [
                "users" => $this->usersService->getUsers($searchQuery)
            ]);
        } catch (Throwable $t) {
            Log::error("Erreur lors de la récupération des utilisateurs", [
                'error' => $t->getMessage()
            ]);
            return redirect()->back()->with("error", "Impossible de charger la liste des utilisateurs.");
        }
    }

    public function store(Request $request, $id = null) {
        $validatedData = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'max:255', 'email'],
            'departements' => ['sometimes', 'array'],
        ]);

        try {
            if($id) {
                $this->usersService->update($id, $validatedData);
                return redirect()->route("admin.user")
                    ->with("success", "Utilisateur mis à jour avec succès.");
            } else {
                try {
                    $this->usersService->handleUserInDatabase($validatedData);
                    return redirect()->route("admin.user")
                        ->with("success", "Utilisateur créé avec succès.");
                } catch (UnauthorizedException) {
                    return redirect()->back()->with("error", "Cette utilisateur ne fais pas partie de 12Plus");
                }

            }

        } catch (BadRequestException $e) {
            Log::warning("Requête invalide lors de la gestion utilisateur", [
                'id' => $id,
                'email' => $validatedData['email']
            ]);
            return redirect()->back()->with('error', 'Les données fournies sont incorrectes.');

        } catch (PersistenceException $e) {
            Log::error("Erreur de base de données (User)", [
                'id' => $id,
                'email' => $validatedData['email'],
                'exception' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Erreur lors de la sauvegarde. Veuillez réessayer.');

        } catch (Throwable $t) {
            Log::critical('Erreur fatale utilisateur', [
                'id' => $id,
                'data' => $validatedData,
                'error' => $t->getMessage()
            ]);
            return redirect()->back()->with('error', 'Une erreur imprévue est survenue.');
        }
    }

    public function delete($id) {
        try {
            $this->usersService->delete($id);
            return redirect()->back()->with("success", "Utilisateur supprimé avec succès.");

        } catch (BadRequestException) {
            return redirect()->back()->with('error', 'Impossible de supprimer cet utilisateur (ID invalide).');

        } catch (PersistenceException $e) {
            Log::error("Échec de suppression utilisateur", ['id' => $id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Erreur technique lors de la suppression.');

        } catch (Throwable $t) {
            Log::critical('Crash lors de la suppression utilisateur', [
                'id' => $id,
                'error' => $t->getMessage()
            ]);
            return redirect()->back()->with('error', 'Une erreur imprévue est survenue.');
        }
    }
}
