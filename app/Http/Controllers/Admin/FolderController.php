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

class FolderController extends Controller {
    public function __construct(
        private readonly FoldersServiceInterface $foldersService
    ) {}

    public function create($folder_id) {
        if($folder_id != 0) {
            try {
                $this->foldersService->read($folder_id);
            } catch (FolderNotFoundException $e) {
                return redirect()->back()->with("success" , "Argument(s) incorrect(s)");
            }
        }

        return \Inertia\Inertia::render('Admin/FolderForm', [
            "parent_id" => $folder_id,
        ]);
    }

    public function update(int $folder_id, int $id) {
        if(empty($folder_id) && $folder_id != 0) {
            return redirect()->back()->with("success", "Argument(s) manquant(s)");
        }
        try {
            $this->foldersService->read($folder_id);
            $folder = $this->foldersService->read($id);
        } catch (FolderNotFoundException) {
            return redirect()->back()->with("success" , "Argument(s) incorrect(s)");
        }

        return \Inertia\Inertia::render('Admin/FolderForm', [
            "folder" => $folder,
            "parent_id" => $folder_id,
        ]);
    }

    public function store(Request $request, $folder_id, $id = null) {
        // 1. Validation de la requête
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'max:16'],
        ]);

        if(!empty($folder_id)) {
            $validatedData['parent_id'] = $folder_id;
        } else {
            $validatedData['parent_id'] = null;
        }

        try {
            if($id) {
                $this->foldersService->update($id, $validatedData);
                return redirect()->route("navigation", ["folder_id" => $folder_id])
                    ->with("success", "Folder updated successfully");
            } else {
                $this->foldersService->create($validatedData);
                return redirect()->route("navigation", ["folder_id" => $folder_id])
                    ->with("success", "Folder created successfully");
            }

        } catch (BadRequestException $e) {
            // 400 Bad Request (pour une erreur d'argument si non gérée par la validation)
            return redirect()->back()->with(['success' => 'Arguments manquants ou invalides.'. $e->getMessage()]);

        } catch (FolderNotFoundException) {
            // 404 Not Found (Ressource à mettre à jour non trouvée)
            return redirect()->back()->with(['success' => 'Le dossier ou un attachement spécifié est introuvable.']);

        } catch (PersistenceException $e) {
            // 500 Internal Server Error (Erreur BD ou Disque)
            return redirect()->back()->with(['success' => 'Erreur critique de sauvegarde des données. Veuillez réessayer. '. $e->getMessage()]);

        } catch (Throwable $t) {
            // Erreur imprévue (la transaction a été rollback dans le service)
            Log::critical('Unhandled fatal error during folder transaction.', ['error' => $t->getMessage(), 'id' => $id]);
            return redirect()->back()->with(['success' => 'Une erreur imprévue est survenue (Code: 500).']);
        }
    }
}
