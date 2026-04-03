<?php

namespace App\Http\Controllers\Admin;

use App\Exception\DepartementNotFoundException;
use App\Exception\PersistenceException;
use App\Exception\UserNotFoundException;
use App\Http\Controllers\Controller;
use App\Services\Interfaces\DepartementsServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Response;
use Throwable;
use Inertia\Inertia;

class DepartementController extends Controller {
    public function __construct(
        private readonly DepartementsServiceInterface $departementsService,
    ){}

    /**
     * Renvoie une réponse Inertia contenant la Vue pour afficher la liste des départements
     * @return RedirectResponse|Response
     */
    public function readAll() {
        try {
            // Départements déjà passer en tant que variable global,
            // on ne les repasse ici ne pas faire d'overfetching ou de requête inutile
            return Inertia::render("Admin/Departements");
        } catch (Throwable $t) {
            Log::error("Erreur lors du rendu de la page des départements", [
                'exception' => $t->getMessage(),
                'trace' => $t->getTraceAsString()
            ]);
            return redirect()->back()->with("error", "Impossible de charger la page des départements.");
        }
    }

    public function users($id) {
        try {
            $users = $this->departementsService->getUsers($id);
            $departement = $this->departementsService->readById($id);
        } catch (DepartementNotFoundException) {
            return redirect()->back()->with("error", "Departement inexistant");
        }
        return Inertia::render("Admin/UsersByDepartement", [
            "users" => $users,
            "departement" => $departement
        ]);
    }

    /**
     * Fonction se chargeant de fournir les données provenant de la requête au service pour
     * soit créer, soit mettre à jour un Departement
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function store(Request $request, $id = null) {
        $validatedData = $request->validate([
            'initials' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        if (empty($validatedData["initials"]) || empty($validatedData["name"])) {
            // On génère un message flash si la requête n'est pas bonne
            return redirect()->back()->with(["error" => "Il manque des informations obligatoires."]);
        }

        try {
            if($id) {
                // Si on a un ID on update
                $this->departementsService->update($id, $validatedData);
                return redirect()->route("admin.departements")
                    ->with("success", "Département mis à jour avec succès.");
            } else {
                // Sinon on créait
                $this->departementsService->create($validatedData);
                return redirect()->route("admin.departements")
                    ->with("success", "Département créé avec succès.");
            }

        } catch (DepartementNotFoundException) {
            // On génère un message flash si on ne trouve pas le departement
            return redirect()->back()->with('error', 'Les données fournies sont invalides.');

        } catch (PersistenceException) {
            // On génère un message flash si on n'arrive pas a persisté les modifications
            return redirect()->back()->with('error', 'Erreur technique lors de la sauvegarde. Veuillez réessayer.');

        } catch (Throwable $t) {
            // On log l'erreur un, on génère un message flash si une erreur imprévu est arrivé
            Log::critical('Erreur non gérée lors de la gestion du département', [
                'id' => $id,
                'data' => $validatedData,
                'error' => $t->getMessage(),
                'file' => $t->getFile(),
                'line' => $t->getLine()
            ]);
            return redirect()->back()->with('error', 'Une erreur imprévue est survenue.');
        }
    }

    /**
     * Fonction pour lancer une suppression d'un Departement dont l'id est donné en paramètre
     * @param $id
     * @return RedirectResponse
     */
    public function delete($id) {
        if (empty($id)) {
            // Si aucun $id n'est donné, on génère un flash message
            return redirect()->back()->with("error", "Aucun departement spécifié");
        }

        try {
            // On lance la suppression
            $this->departementsService->delete($id);
            return redirect()->back()->with("success", "Département supprimé avec succès.");

        } catch (DepartementNotFoundException) {
            // Si le departement n'éxiste pas, on génère un message flash d'erreur
            return redirect()->back()->with('error', 'Le département spécifié est introuvable.');

        } catch (PersistenceException) {
            // Si la suppression ne fonctionne pas on génère un message flash
            return redirect()->back()->with('error', 'Le département ne peut pas être supprimé actuellement.');

        } catch (Throwable $t) {
            // Si une erreur inconnue est throw, on la log et on génère un message flash
            Log::critical('Erreur fatale lors de la suppression du département', [
                'id' => $id,
                'error' => $t->getMessage()
            ]);
            return redirect()->back()->with('error', 'Une erreur imprévue est survenue.');
        }
    }

    public function removeUser(string $id, string $user_id)
    {
        if(!$id || !$user_id) {
            return redirect()->back()->with(["error" => "Veuillez spécifier un departement ET un utilisateur"]);
        }

        try {
            $this->departementsService->removeUser($id, $user_id);
            return redirect()->back()->with(["success" => "Utilisateur retirer du departement."]);
        } catch (DepartementNotFoundException) {
            return redirect()->back()->with(["error" => "Departement inexistant"]);
        } catch (PersistenceException) {
            return redirect()->back()->with(["error" => "Erreur lors de l'enregistrement de la modification"]);
        } catch (UserNotFoundException) {
            return redirect()->back()->with(["error" => "Utilisateur inexistant"]);
        }
    }
}
