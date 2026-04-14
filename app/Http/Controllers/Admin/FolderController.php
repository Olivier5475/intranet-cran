<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveFolderRequest;
use App\Services\Interfaces\FoldersServiceInterface;
use App\Exception\{FolderNotFoundException, PersistenceException};
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Throwable;

class FolderController extends Controller
{
    public function __construct(
        private readonly FoldersServiceInterface $foldersService
    ) {}

    // --- VUES (GET) ---

    public function create(int $parent_id)
    {
        // On vérifie juste l'existence du parent s'il n'est pas à la racine (0)
        if ($parent_id !== 0) {
            try {
                $this->foldersService->read($parent_id);
            } catch (FolderNotFoundException) {
                return redirect()->back()->with("error", "Le dossier parent est introuvable.");
            }
        }

        return Inertia::render('Admin/FolderForm', ["parent_id" => $parent_id]);
    }

    public function edit(int $id)
    {
        try {
            $folder = $this->foldersService->read($id);
            return Inertia::render('Admin/FolderForm', ["folder" => $folder]);
        } catch (FolderNotFoundException) {
            return redirect()->back()->with("error", "Ce dossier n'existe pas.");
        }
    }

    // --- ACTIONS (POST / PUT) ---

    public function store(SaveFolderRequest $request)
    {
        try {
            $this->foldersService->create($request->validated());

            return redirect()->route("navigate.folder", ["folder_id" => $request->input('parent_id') ?? 0])
                ->with("success", "Le dossier a été créé avec succès.");
        } catch (Throwable $t) {
            return $this->handleException($t, "création");
        }
    }

    public function update(SaveFolderRequest $request, int $id)
    {
        try {
            $this->foldersService->update($id, $request->validated());

            return redirect()->route("navigate.folder", ["folder_id" => $id])
                ->with("success", "Le dossier a été mis à jour avec succès.");
        } catch (Throwable $t) {
            return $this->handleException($t, "mise à jour", $id);
        }
    }

    // --- SUPPRESSION / RESTAURATION ---

    public function delete(int $id)
    {
        try {
            // La sécurité logicielle (hasEditAccess) peut être mise ici ou en middleware
            if (!$this->foldersService->hasEditAccess($id)) {
                return redirect()->back()->with("error", "Permissions insuffisantes.");
            }

            $this->foldersService->delete($id);
            return redirect()->back()->with("success", "Le dossier a été supprimé avec succès.");
        } catch (Throwable $t) {
            return $this->handleException($t, "suppression", $id);
        }
    }

    public function restore(int $id)
    {
        try {
            $this->foldersService->restore($id);
            return redirect()->back()->with("success", "Le dossier a été restauré avec succès.");
        } catch (Throwable $t) {
            return $this->handleException($t, "restauration", $id);
        }
    }

    // --- HELPER D'ERREURS ---

    private function handleException(Throwable $t, string $action, int $id = null)
    {
        Log::error("Erreur Dossier $action", ['id' => $id, 'error' => $t->getMessage()]);

        $message = match(get_class($t)) {
            FolderNotFoundException::class => "Le dossier cible est introuvable.",
            PersistenceException::class => "Erreur technique lors de la sauvegarde (le dossier est peut-être lié à d'autres éléments).",
            default => "Une erreur imprévue est survenue lors de la $action."
        };

        return redirect()->back()->with('error', $message);
    }
}
