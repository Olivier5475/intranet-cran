<?php

namespace App\Http\Controllers\Admin;

use App\Exception\AlreadyExistsException;
use App\Exception\DiskWriteException;
use App\Exception\FileNotFoundException;
use App\Exception\PersistenceException;
use App\Http\Controllers\Controller;
use App\Services\Interfaces\FilesServiceInterface;
use App\Services\Interfaces\FoldersServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Illuminate\Contracts\Filesystem;
use Throwable;

class FileController extends Controller {

    public function __construct(
        private readonly FilesServiceInterface $filesService,
        private readonly FoldersServiceInterface $foldersService
    ){}

    public function store(Request $request, $file_id = null) {
        // 1. Validation de la requête
        $validatedData = $request->validate([
            'name' => [
                $request->hasFile('files') ? 'nullable' : 'required',
                'string',
                'max:255'
            ],
            'files' => [$file_id ? 'sometimes' : 'required', 'array'],
            'files.*' => ['file', 'max:102400'],
            'departements' => ['sometimes', 'array'],
            'parent_id' => ['integer', 'nullable'],
        ]);

        // 2. Préparation des données pour le Service
        $data = [
            "name" => $validatedData["name"],
            "file" => $request->file('files')[0] ?? null,
            "departements" => $validatedData["departements"] ?? [],
        ];

        if(!is_null($validatedData["parent_id"])) {
            $data["folder_id"] = $validatedData["parent_id"];
        }
        try {
            if($file_id) {
                if(!$this->filesService->hasEditAccess($file_id)) {
                    return redirect()->route("navigate.folder", ["folder_id" => $this->filesService->read($file_id)->folder_id]);
                }
                $file = $this->filesService->update($file_id, $data);
                return redirect()->route("navigate.folder", ["folder_id" => $file->folder_id])
                    ->with("success", "Fichier mis à jour avec success");
            } else {
                $file = $this->filesService->create($data);
                return redirect()->route("navigate.folder", ["folder_id" => $file->folder_id])
                    ->with("success", "Fichier créé avec success");
            }

        } catch (BadRequestException $e) {
            // 400 Bad Request (pour une erreur d'argument si non gérée par la validation)
            return redirect()->back()->with(['error' => 'Arguments manquants ou invalides.']);

        } catch (FileNotFoundException) {
            // 404 Not Found (Ressource à mettre à jour non trouvée)
            return redirect()->back()->with(['error' => 'Le fichier spécifié est introuvable.']);

        } catch (PersistenceException|DiskWriteException $e) {
            // 500 Internal Server Error (Erreur BD ou Disque)
            return redirect()->back()->with(['error' => 'Erreur critique de sauvegarde des données. Veuillez réessayer.']);

        } catch (AlreadyExistsException $e) {
            // 500 Internal Server Error (Erreur BD ou Disque)
            return redirect()->back()->with(['error' => 'Fichier / Document avec le même nom existant']);

        } catch (Throwable $t) {
            // Erreur imprévue (la transaction a été rollback dans le service)
            Log::critical('Unhandled fatal error during file transaction.', ['error' => $t->getMessage(), 'id' => $file_id]);
            return redirect()->back()->with(['error' => 'Une erreur imprévue est survenue réessayer plus tard.']);
        }
    }

    public function create($parent_id) {
        if(!$this->foldersService->hasEditAccess($parent_id)) {
            return redirect()->route("navigate.folder", ["folder_id" => $parent_id])->with("warn" , "Vous n'avez pas le droit de modifier ce dossier");
        }
        return \Inertia\Inertia::render('Admin/FileForm', [
            "parent_id" => $parent_id
        ]);
    }
    public function update($file_id) {
        try {
            if(!$this->filesService->hasEditAccess($file_id)) {
                return redirect()->route("navigate.folder", ["folder_id" => $this->filesService->read($file_id)->folder_id]);
            }
            return \Inertia\Inertia::render('Admin/FileForm', [
                "file" => $this->filesService->read($file_id),
            ]);
        } catch (BadRequestException $e) {
            // 400 Bad Request (pour une erreur d'argument si non gérée par la validation)
            return redirect()->back()->with(['error' => 'Arguments manquants ou invalides.']);
        } catch (FileNotFoundException $e) {
            // 404 Not Found
            return redirect()->back()->with(['error' => 'Le document ou un attachement spécifié est introuvable.']);
        }
    }

    public function delete($file_id) {
        try {
            if(!$this->filesService->hasEditAccess($file_id)) {
                return redirect()->route("navigate.folder", ["folder_id" => $this->filesService->read($file_id)->folder_id]);
            }
            $this->filesService->delete($file_id);
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
