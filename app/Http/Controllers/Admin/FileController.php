<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveFileRequest;
use App\Services\Interfaces\{FilesServiceInterface, FoldersServiceInterface};
use App\Exception\{AlreadyExistsException, FileNotFoundException, PersistenceException, DiskWriteException};
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Throwable;

class FileController extends Controller {

    public function __construct(
        private readonly FilesServiceInterface $filesService,
    ){}

    // --- VUES (GET) ---

    public function create(int $parent_id) {
        // La sécurité est déjà gérée par le middleware ou peut être ajoutée ici
        return Inertia::render('Admin/FileForm', ["parent_id" => $parent_id]);
    }

    public function edit(int $id) {
        try {
            return Inertia::render('Admin/FileForm', [
                "file" => $this->filesService->read($id)
            ]);
        } catch (Throwable $t) {
            return redirect()->back()->with('error', 'Impossible de charger le fichier.');
        }
    }

    // --- ACTIONS (POST / PUT) ---

    public function store(SaveFileRequest $request) {
        try {
            $file = $this->filesService->create($request->toServiceData());
            return redirect()->route("navigate.folder", ["folder_id" => $file->folder_id])
                ->with("success", "Le fichier a été créé avec succès.");
        } catch (Throwable $t) {
            return $this->handleException($t, "création");
        }
    }

    public function update(SaveFileRequest $request, int $id) {
        try {
            $file = $this->filesService->update($id, $request->toServiceData());
            return redirect()->route("navigate.folder", ["folder_id" => $file->folder_id])
                ->with("success", "Le fichier a été mis à jour avec succès.");
        } catch (Throwable $t) {
            return $this->handleException($t, "mise à jour", $id);
        }
    }

    // --- SUPPRESSION / RESTAURATION ---

    public function delete(int $id) {
        try {
            $this->filesService->delete($id);
            return redirect()->back()->with("success", "Le fichier a été supprimé avec succès.");
        } catch (Throwable $t) {
            return $this->handleException($t, "suppression", $id);
        }
    }

    public function restore(int $id) {
        try {
            $this->filesService->restore($id);
            return redirect()->back()->with("success", "Le fichier a été restauré avec succès.");
        } catch (Throwable $t) {
            return $this->handleException($t, "restauration", $id);
        }
    }

    // --- HELPERS ---

    private function handleException(Throwable $t, string $action, int $id = null) {
        Log::error("Erreur Fichier $action", ['id' => $id, 'error' => $t->getMessage()]);

        $message = match(get_class($t)) {
            AlreadyExistsException::class => "Un fichier avec ce nom existe déjà dans ce dossier.",
            FileNotFoundException::class => "Le fichier spécifié est introuvable.",
            PersistenceException::class, DiskWriteException::class => "Erreur technique lors de l'enregistrement.",
            default => "Une erreur imprévue est survenue."
        };

        return redirect()->back()->with('error', $message);
    }
}
