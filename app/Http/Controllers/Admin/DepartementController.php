<?php

namespace App\Http\Controllers\Admin;

use App\Exception\DepartementNotFoundException;
use App\Exception\PersistenceException;
use App\Http\Controllers\Controller;
use App\Services\Interfaces\DepartementsServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Throwable;
use Inertia\Inertia;

class DepartementController extends Controller {
    public function __construct(
        private readonly DepartementsServiceInterface $departementsService,
    ){}

    public function readAll() {
        try {
            return Inertia::render("Admin/Departements");
        } catch (Throwable $t) {
            Log::error("Erreur lors du rendu de la page des départements", [
                'exception' => $t->getMessage(),
                'trace' => $t->getTraceAsString()
            ]);
            return redirect()->back()->with("error", "Impossible de charger la page des départements.");
        }
    }

    public function store(Request $request, $id = null) {
        $validatedData = $request->validate([
            'initials' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        try {
            if($id) {
                $this->departementsService->update($id, $validatedData);
                return redirect()->route("admin.departements")
                    ->with("success", "Département mis à jour avec succès.");
            } else {
                $this->departementsService->create($validatedData);
                return redirect()->route("admin.departements")
                    ->with("success", "Département créé avec succès.");
            }

        } catch (BadRequestException $e) {
            Log::warning("Requête invalide lors de l'enregistrement d'un département", [
                'id' => $id,
                'data' => $validatedData,
                'message' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Les données fournies sont invalides.');

        } catch (PersistenceException $e) {
            Log::error("Erreur de persistance lors de l'enregistrement du département", [
                'id' => $id,
                'data' => $validatedData,
                'exception' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Erreur technique lors de la sauvegarde. Veuillez réessayer.');

        } catch (Throwable $t) {
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

    public function delete($id) {
        try {
            $this->departementsService->delete($id);
            return redirect()->back()->with("success", "Département supprimé avec succès.");

        } catch (DepartementNotFoundException $e) {
            Log::notice("Tentative de suppression d'un département inexistant", ['id' => $id]);
            return redirect()->back()->with('error', 'Le département spécifié est introuvable.');

        } catch (PersistenceException $e) {
            Log::error("Échec de la suppression du département en base de données", [
                'id' => $id,
                'exception' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Le département ne peut pas être supprimé actuellement.');

        } catch (Throwable $t) {
            Log::critical('Erreur fatale lors de la suppression du département', [
                'id' => $id,
                'error' => $t->getMessage()
            ]);
            return redirect()->back()->with('error', 'Une erreur imprévue est survenue.');
        }
    }
}
