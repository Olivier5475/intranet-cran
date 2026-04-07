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
use Throwable;
use Inertia\Inertia;

class FileController extends Controller {

    public function __construct(
        private readonly FilesServiceInterface $filesService,
        private readonly FoldersServiceInterface $foldersService
    ){}

    public function store(Request $request, $file_id = null) {
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

        $data = [
            "name" => $validatedData["name"],
            "file" => $request->file('files')[0] ?? null,
            "departements" => $validatedData["departements"] ?? [],
            "folder_id" => $validatedData["parent_id"] ?? null,
        ];

        try {
            if($file_id) {
                if(!$this->filesService->hasEditAccess($file_id)) {
                    Log::notice("Accès refusé pour la mise à jour du fichier", ['file_id' => $file_id, 'user_id' => auth()->id()]);
                    return redirect()->route("navigate.folder", ["folder_id" => $this->filesService->read($file_id)->folder_id])
                        ->with("error", "Vous n'avez pas les permissions pour modifier ce fichier.");
                }
                $file = $this->filesService->update($file_id, $data);
                return redirect()->route("navigate.folder", ["folder_id" => $file->folder_id])
                    ->with("success", "Le fichier a été mis à jour avec succès.");
            } else {
                $file = $this->filesService->create($data);
                return redirect()->route("navigate.folder", ["folder_id" => $file->folder_id])
                    ->with("success", "Le fichier a été créé avec succès.");
            }

        } catch (BadRequestException $e) {
            Log::warning("Requête invalide lors du traitement du fichier", ['file_id' => $file_id, 'message' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Les données du fichier sont incorrectes ou incomplètes.');

        } catch (FileNotFoundException $e) {
            Log::error("Fichier introuvable lors de l'opération", ['file_id' => $file_id]);
            return redirect()->back()->with('error', 'Le fichier spécifié est introuvable.');

        } catch (PersistenceException|DiskWriteException $e) {
            Log::error("Erreur de stockage ou base de données pour un fichier", [
                'file_id' => $file_id,
                'exception' => $e->getMessage(),
                'data' => array_diff_key($data, ['file' => '']) // On ne logue pas l'objet File
            ]);
            return redirect()->back()->with('error', 'Une erreur technique est survenue lors de l’enregistrement.');

        } catch (AlreadyExistsException $e) {
            return redirect()->back()->with('error', 'Un fichier avec ce nom existe déjà dans ce dossier.');

        } catch (Throwable $t) {
            Log::critical('Erreur fatale imprévue (File Store)', [
                'file_id' => $file_id,
                'error' => $t->getMessage(),
                'trace' => $t->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Une erreur imprévue est survenue.');
        }
    }

    public function create($parent_id) {
        if(!$this->foldersService->hasEditAccess($parent_id)) {
            Log::notice("Tentative de création de fichier sans droits", ['parent_id' => $parent_id]);
            return redirect()->route("navigate.folder", ["folder_id" => $parent_id])
                ->with("error" , "Vous n'avez pas le droit d'ajouter des fichiers dans ce dossier.");
        }
        return Inertia::render('Admin/FileForm', ["parent_id" => $parent_id]);
    }

    public function update($file_id) {
        try {
            if(!$this->filesService->hasEditAccess($file_id)) {
                $file = $this->filesService->read($file_id);
                Log::notice("Accès refusé au formulaire d'édition de fichier", ['file_id' => $file_id]);
                return redirect()->route("navigate.folder", ["folder_id" => $file->folder_id])
                    ->with("error", "Vous n'avez pas les droits de modification sur ce fichier.");
            }
            return Inertia::render('Admin/FileForm', ["file" => $this->filesService->read($file_id)]);

        } catch (FileNotFoundException $e) {
            return redirect()->back()->with('error', 'Le fichier est introuvable.');
        } catch (Throwable $t) {
            Log::error("Erreur chargement formulaire fichier", ['file_id' => $file_id, 'error' => $t->getMessage()]);
            return redirect()->back()->with('error', 'Impossible de charger le fichier.');
        }
    }

    public function delete($file_id) {
        try {
            if(!$this->filesService->hasEditAccess($file_id)) {
                Log::notice("Tentative de suppression de fichier non autorisée", ['file_id' => $file_id]);
                return redirect()->back()->with("error", "Permissions insuffisantes pour supprimer ce fichier.");
            }
            $this->filesService->delete($file_id);
            return redirect()->back()->with("success", "Le fichier a été supprimé avec succès.");

        } catch (FileNotFoundException $e) {
            return redirect()->back()->with('error', 'Le fichier est déjà supprimé ou introuvable.');

        } catch (PersistenceException|DiskWriteException $e) {
            Log::error("Échec de suppression du fichier", ['file_id' => $file_id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Erreur technique lors de la suppression.');

        } catch (Throwable $t) {
            Log::critical('Erreur fatale lors de la suppression du fichier', ['file_id' => $file_id, 'error' => $t->getMessage()]);
            return redirect()->back()->with('error', 'Une erreur imprévue est survenue (Code 500).');
        }
    }

    public function restore(string $file_id)
    {
        try {
            if(!$this->filesService->hasEditAccess($file_id)) {
                Log::notice("Tentative de suppression de fichier non autorisée", ['file_id' => $file_id]);
                return redirect()->back()->with("error", "Permissions insuffisantes pour supprimer ce fichier.");
            }
            $this->filesService->restore($file_id);
            return redirect()->back()->with("success", "Le fichier a été supprimé avec succès.");

        } catch (FileNotFoundException $e) {
            return redirect()->back()->with('error', 'Le fichier est déjà supprimé ou introuvable.');

        } catch (PersistenceException|DiskWriteException $e) {
            Log::error("Échec de suppression du fichier", ['file_id' => $file_id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Erreur technique lors de la suppression.');

        } catch (Throwable $t) {
            Log::critical('Erreur fatale lors de la suppression du fichier', ['file_id' => $file_id, 'error' => $t->getMessage()]);
            return redirect()->back()->with('error', 'Une erreur imprévue est survenue (Code 500).');
        }
    }
}
