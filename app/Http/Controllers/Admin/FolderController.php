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

class FolderController extends Controller
{
    public function __construct(
        private readonly FoldersServiceInterface $foldersService
    )
    {
    }

    public function create($parent_id)
    {
        if($parent_id != 0) {
            if(!$this->foldersService->hasEditAccess($parent_id)) {
                return redirect()->route("navigate.folder", ["folder_id" => $parent_id])->with("warn" , "Vous n'avez pas le droit de modifier ce dossier");
            }
            try {
                $this->foldersService->read($parent_id);
            } catch (FolderNotFoundException $e) {
                return redirect()->back()->with("success" , "Argument(s) incorrect(s)");
            }
        }

        return \Inertia\Inertia::render('Admin/FolderForm', [
            "parent_id" => $parent_id,
        ]);
    }

    public function update(int $folder_id)
    {
        if(!$this->foldersService->hasEditAccess($folder_id)) {
            return redirect()->route("navigate.folder", ["folder_id" => $folder_id])->with("warn" , "Vous n'avez pas le droit de modifier ce dossier");
        }
        if(empty($folder_id) && $folder_id != 0) {
            return redirect()->back()->with("success", "Argument(s) manquant(s)");
        }
        try {
            $folder = $this->foldersService->read($folder_id);
        } catch (FolderNotFoundException) {
            return redirect()->back()->with("success" , "Argument(s) incorrect(s)");
        }

        return \Inertia\Inertia::render('Admin/FolderForm', [
            "folder" => $folder,
        ]);
    }

    public function store(Request $request, $id = null)
    {
        // 1. Validation de la requête
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'max:16'],
            'parent_id' => ['integer', 'nullable'],
            'departements' => ['sometimes', 'array'],
        ]);
        if(!$this->foldersService->hasEditAccess($id ?? $validatedData["parent_id"])) {
            return redirect()->route("navigate.folder", ["folder_id" => $id ?? $validatedData["parent_id"]])->with("warn" , "Vous n'avez pas le droit de créer ou de modifier dans ce dossier");
        }

        if(is_null($validatedData["parent_id"])) {
            unset($validatedData["parent_id"]);
        }

        try {
            if($id) {
                $this->foldersService->update($id, $validatedData);
                return redirect()->route("navigate.folder", ["folder_id" => $id])
                    ->with("success", "Folder updated successfully");
            } else {
                $this->foldersService->create($validatedData);
                return redirect()->route("navigate.folder", ["folder_id" => $validatedData["parent_id"]])
                    ->with("success", "Folder created successfully");
            }

        } catch (BadRequestException $e) {
            // 400 Bad Request (pour une erreur d'argument si non gérée par la validation)
            return redirect()->back()->with(['error' => 'Arguments manquants ou invalides.'. $e->getMessage()]);

        } catch (FolderNotFoundException) {
            // 404 Not Found (Ressource à mettre à jour non trouvée)
            return redirect()->back()->with(['error' => 'Le dossier ou un attachement spécifié est introuvable.']);

        } catch (PersistenceException $e) {
            // 500 Internal Server Error (Erreur BD ou Disque)
            return redirect()->back()->with(['error' => 'Erreur critique de sauvegarde des données. Veuillez réessayer. '. $e->getMessage()]);

        } catch (Throwable $t) {
            // Erreur imprévue (la transaction a été rollback dans le service)
            Log::critical('Unhandled fatal error during folder transaction.', ['error' => $t->getMessage(), 'id' => $id]);
            return redirect()->back()->with(['error' => 'Une erreur imprévue est survenue (Code: 500).']);
        }
    }

    public function delete(int $folder_id)
    {
        if(!$this->foldersService->hasEditAccess($folder_id)) {
            return redirect()->back()->with("warn" , "Vous n'avez pas le droit de supprimer ce dossier !");
        }

        try {
            $this->foldersService->delete($folder_id);
        } catch (FolderNotFoundException) {
            return redirect()->back()->with("error" , "Dossier introuvable ou inexistant !");
        } catch (PersistenceException) {
            return redirect()->back()->with("error", "Une erreur est survenu. Impossible de supprimer le dossier");
        } catch (BadRequestException) {
            return redirect()->back()->with("error", "Erreur de requête, veuillez réessayer.");
        }

        return redirect()->back()->with("success", "Dossier supprimer avec succès");
    }
}
