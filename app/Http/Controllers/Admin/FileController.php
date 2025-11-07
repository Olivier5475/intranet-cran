<?php

namespace App\Http\Controllers\Admin;

use App\Exception\DiskWriteException;
use App\Exception\FileNotFoundException;
use App\Exception\PersistenceException;
use App\Http\Controllers\Controller;
use App\Services\Interfaces\FilesServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Illuminate\Contracts\Filesystem;
use Throwable;

class FileController extends Controller {
    public function __construct(
        private readonly FilesServiceInterface $filesService,
    ){}

    public function store(Request $request, int $folder_id, $id = null) {
        // 1. Validation de la requête
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'files' => ['sometimes', 'array'],
            'files.*' => ['file', 'max:51200'],
        ]);

        // 2. Préparation des données pour le Service
        $data = [
            "name" => $validatedData["name"],
            "file" => $request->file('files')[0],
            "folder_id" => $folder_id,
        ];

        try {
            if($id) {
                $this->filesService->update($id, $data);
                return redirect()->route("home")
                    ->with("success", "Fichier mis à jour avec success");
            } else {
                $this->filesService->create($data);
                return redirect()->route("home")
                    ->with("success", "Fichier créé avec success");

            }

        } catch (BadRequestException $e) {
            // 400 Bad Request (pour une erreur d'argument si non gérée par la validation)
            return redirect()->back()->with(['success' => 'Arguments manquants ou invalides.'. $e->getMessage()]);

        } catch (FileNotFoundException) {
            // 404 Not Found (Ressource à mettre à jour non trouvée)
            return redirect()->back()->with(['success' => 'Le fichier spécifié est introuvable.']);

        } catch (PersistenceException|DiskWriteException $e) {
            // 500 Internal Server Error (Erreur BD ou Disque)
            return redirect()->back()->with(['success' => 'Erreur critique de sauvegarde des données. Veuillez réessayer. '. $e->getMessage()]);

        } catch (Throwable $t) {
            // Erreur imprévue (la transaction a été rollback dans le service)
            Log::critical('Unhandled fatal error during file transaction.', ['error' => $t->getMessage(), 'id' => $id]);
            return redirect()->back()->with(['success' => 'Une erreur imprévue est survenue (Code: 500).']);
        }
    }

    public function create($folder_id) {
        return \Inertia\Inertia::render('Admin/FileForm', [
            "folder_id" => $folder_id
        ]);
    }
    public function update($folder_id, $id) {
        try {
            return \Inertia\Inertia::render('Admin/FileForm', [
                "file_name" => $this->filesService->readName($id),
                "folder_id" => $folder_id
            ]);
        } catch (BadRequestException $e) {
            // 400 Bad Request (pour une erreur d'argument si non gérée par la validation)
            return redirect()->back()->with(['error' => 'Arguments manquants ou invalides.']);
        } catch (FileNotFoundException $e) {
            // 404 Not Found
            return redirect()->back()->with(['error' => 'Le document ou un attachement spécifié est introuvable.']);
        }
    }

    public function delete($id) {
        try {
            $this->filesService->delete($id);
            return redirect()->back()->with("success", "Document deleted successfully");
        } catch (BadRequestException) {
            // 400 Bad Request (pour une erreur d'argument si non gérée par la validation)
            return redirect()->back()->with(['error' => 'Arguments manquants ou invalides.']);

        } catch (FileNotFoundException) {
            // 404 Not Found (Ressource à mettre à jour non trouvée)
            return redirect()->back()->with(['error' => 'Le document ou un attachement spécifié est introuvable.']);

        } catch (PersistenceException|DiskWriteException) {
            // 500 Internal Server Error (Erreur BD ou Disque)
            return redirect()->back()->with(['error' => 'Erreur critique de sauvegarde des données. Veuillez réessayer.']);

        } catch (Throwable $t) {
            // Erreur imprévue (la transaction a été rollback dans le service)
            Log::critical('Unhandled fatal error during document transaction.', ['error' => $t->getMessage(), 'id' => $id]);
            return redirect()->back()->with(['error' => 'Une erreur imprévue est survenue (Code: 500).']);
        }
    }
}
