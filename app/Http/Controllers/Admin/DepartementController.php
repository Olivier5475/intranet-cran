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

class DepartementController extends Controller {
    public function __construct(
        private readonly DepartementsServiceInterface $departementsService,
    ){}

    public function readAll() {
        try {
            return \Inertia\Inertia::render("Admin/Departements");
        } catch (\Exception) {
            return redirect()->back()->with("message", "No users found");
        }
    }

    public function store(Request $request, $id = null) {
        // 1. Validation de la requête
        $validatedData = $request->validate([
            'initials' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
        ]);
        try {
            if($id) {
                $this->departementsService->update($id, $validatedData);
                return redirect()->route("admin.departements")
                    ->with("success", "Utilisateur mis à jour avec success");
            } else {
                $this->departementsService->create($validatedData);
                return redirect()->route("admin.departements")
                    ->with("success", "Utilisateur créé avec success");
            }

        } catch (BadRequestException $e) {
            // 400 Bad Request (pour une erreur d'argument si non gérée par la validation)
            return redirect()->back()->with(['success' => 'Arguments manquants ou invalides.'. $e->getMessage()]);

        } catch (PersistenceException $e) {
            // 500 Internal Server Error (Erreur BD ou Disque)
            return redirect()->back()->with(['success' => 'Erreur critique de sauvegarde des données. Veuillez réessayer. '. $e->getMessage()]);

        } catch (Throwable $t) {
            // Erreur imprévue (la transaction a été rollback dans le service)
            Log::critical('Fatal Error', [
                'error' => $t, 'id' => $id,
                'nom' => $validatedData['nom'],
                'prenom' => $validatedData['prenom'],
                'role' => $validatedData['role'],
                'email' => $validatedData['email'],
            ]);
            return redirect()->back()->with(['success' => 'Une erreur imprévue est survenue (Code: 500).']);
        }
    }

    public function create() {
        return \Inertia\Inertia::render('Admin/DepartementForm');
    }
    public function update($id) {
        try {
            return \Inertia\Inertia::render('Admin/DepartementForm', [
                "departement" => $this->departementsService->readById($id)
            ]);
        } catch (BadRequestException $e) {
            // 400 Bad Request (pour une erreur d'argument si non gérée par la validation)
            return redirect()->back()->with(['error' => 'Arguments manquants ou invalides.']);
        } catch (DepartementNotFoundException $e) {
            // 404 Not Found
            return redirect()->back()->with(['error' => 'Le departement spécifié est introuvable.']);
        }
    }

    public function delete($id) {
        try {
            $this->departementsService->delete($id);
            return redirect()->back()->with("success", "Document deleted successfully");
        } catch (DepartementNotFoundException $e) {
            // 404 Not Found
            return redirect()->back()->with(['error' => 'Le departement spécifié est introuvable.']);
        } catch (BadRequestException) {
            // 400 Bad Request (pour une erreur d'argument si non gérée par la validation)
            return redirect()->back()->with(['error' => 'Arguments manquants ou invalides.']);
        } catch (PersistenceException) {
            // 500 Internal Server Error (Erreur BD ou Disque)
            return redirect()->back()->with(['error' => 'Erreur critique de sauvegarde des données. Veuillez réessayer.']);
        } catch (Throwable $t) {
            // Erreur imprévue (la transaction a été rollback dans le service)
            Log::critical('Unhandled fatal error during document transaction.', ['error' => $t->getMessage(), 'id' => $id]);
            return redirect()->back()->with(['error' => 'Une erreur imprévue est survenue (Code: 500).']);
        }
    }
}
