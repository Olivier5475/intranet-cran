<?php

namespace App\Http\Controllers\Admin;

use App\Exception\FolderNotFoundException;
use App\Exception\PersistenceException;
use App\Http\Controllers\Controller;
use App\Services\Interfaces\FoldersServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Throwable;
use Inertia\Inertia;

class FolderController extends Controller
{
    public function __construct(
        private readonly FoldersServiceInterface $foldersService
    ) {}

    public function create($parent_id)
    {
        if ($parent_id != 0) {
            if (!$this->foldersService->hasEditAccess($parent_id)) {
                Log::notice("Accès refusé pour la création dans un dossier", ['parent_id' => $parent_id, 'user_id' => auth()->id()]);
                return redirect()->route("navigate.folder", ["folder_id" => $parent_id])
                    ->with("error", "Vous n'avez pas les permissions nécessaires pour créer un dossier ici.");
            }
            try {
                $this->foldersService->read($parent_id);
            } catch (FolderNotFoundException) {
                return redirect()->back()->with("error", "Le dossier parent est introuvable.");
            }
        }

        return Inertia::render('Admin/FolderForm', [
            "parent_id" => $parent_id,
        ]);
    }

    public function update(int $folder_id)
    {
        if (!$this->foldersService->hasEditAccess($folder_id)) {
            Log::notice("Accès refusé pour la modification du dossier", ['folder_id' => $folder_id]);
            return redirect()->route("navigate.folder", ["folder_id" => $folder_id])
                ->with("error", "Vous n'avez pas le droit de modifier ce dossier.");
        }

        try {
            $folder = $this->foldersService->read($folder_id);
            return Inertia::render('Admin/FolderForm', [
                "folder" => $folder,
            ]);
        } catch (FolderNotFoundException) {
            return redirect()->back()->with("error", "Le dossier que vous tentez de modifier n'existe pas.");
        }
    }

    public function store(Request $request, $id = null)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'max:16'],
            'parent_id' => ['integer', 'nullable'],
            'departements' => ['sometimes', 'array'],
        ]);

        $targetId = $id ?? $validatedData["parent_id"];
        if (!$this->foldersService->hasEditAccess($targetId)) {
            return redirect()->route("navigate.folder", ["folder_id" => $targetId])
                ->with("error", "Action non autorisée dans ce dossier.");
        }

        if (empty($validatedData["parent_id"])) {
            unset($validatedData["parent_id"]);
        }

        try {
            if ($id) {
                $this->foldersService->update($id, $validatedData);
                return redirect()->route("navigate.folder", ["folder_id" => $id])
                    ->with("success", "Le dossier a été mis à jour avec succès.");
            } else {
                $this->foldersService->create($validatedData);
                return redirect()->route("navigate.folder", ["folder_id" => $validatedData["parent_id"] ?? 0])
                    ->with("success", "Le dossier a été créé avec succès.");
            }

        } catch (BadRequestException $e) {
            Log::warning("Requête invalide (Folder Store)", ['id' => $id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Les données saisies sont incorrectes.');

        } catch (FolderNotFoundException) {
            return redirect()->back()->with('error', 'Le dossier cible est introuvable.');

        } catch (PersistenceException $e) {
            Log::error("Erreur de persistance dossier", ['id' => $id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Une erreur technique est survenue lors de la sauvegarde.');

        } catch (Throwable $t) {
            Log::critical('Erreur fatale imprévue (Folder Transaction)', [
                'id' => $id,
                'error' => $t->getMessage(),
                'trace' => $t->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Une erreur imprévue est survenue.');
        }
    }

    public function delete(int $folder_id)
    {
        try {
            if (!$this->foldersService->hasEditAccess($folder_id)) {
                Log::notice("Tentative de suppression de dossier non autorisée", ['id' => $folder_id]);
                return redirect()->back()->with("error", "Vous n'avez pas les permissions pour supprimer ce dossier.");
            }

            $this->foldersService->delete($folder_id);
            return redirect()->back()->with("success", "Le dossier a été supprimé avec succès.");

        } catch (FolderNotFoundException) {
            return redirect()->back()->with("error", "Ce dossier n'existe plus ou a déjà été supprimé.");

        } catch (PersistenceException $e) {
            Log::error("Erreur lors de la suppression du dossier", ['id' => $folder_id, 'error' => $e->getMessage()]);
            return redirect()->back()->with("error", "Le dossier ne peut pas être supprimé (vérifiez s'il est vide).");

        } catch (Throwable $t) {
            Log::critical('Erreur fatale lors de la suppression du dossier', ['id' => $folder_id, 'error' => $t->getMessage()]);
            return redirect()->back()->with("error", "Une erreur technique est survenue.");
        }
    }
}
