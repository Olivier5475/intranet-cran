<?php

namespace App\Http\Controllers\Admin;

use App\Exception\AlreadyExistsException;
use App\Exception\AttachmentNotFoundException;
use App\Exception\DiskWriteException;
use App\Exception\DocumentNotFoundException;
use App\Exception\PersistenceException;
use App\Http\Controllers\Controller;
use App\Services\Interfaces\DocumentsServiceInterface;
use App\Services\Interfaces\FoldersServiceInterface;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Throwable;

class DocumentController extends Controller {
    public function __construct(
        private readonly DocumentsServiceInterface $documentsService,
        private readonly FoldersServiceInterface $foldersService,
    ){}

    public function store(Request $request, $id = null) {
        // 1. Validation de la requête
        $validatedData = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'color' => ['string', 'max:16'],

            // NOUVEAU CHAMP : Tableau des objets d'attachements existants
            'existing_attachments' => ['sometimes', 'array'],

            // VALIDATION DU TABLEAU D'OBJETS
            // Chaque élément (index) du tableau doit être un tableau (objet JSON)
            'existing_attachments.*' => ['array'],

            // Validation des PROPRIÉTÉS de chaque objet
            'existing_attachments.*.id' => ['required', 'integer', 'exists:attachments,id'],
            'existing_attachments.*.name' => ['required', 'string', 'max:255'],

            // ... validation des nouveaux fichiers inchangée
            'new_attachments' => ['sometimes', 'array'],
            'new_attachments.*' => ['file', 'max:51200'],

            'departements' => ['sometimes', 'array'],
            'parent_id' => ['integer', 'nullable'],
        ]);
        // 2. Préparation des données pour le Service
        if(empty($validatedData['color'])) {
            if(empty($id)) {
                $validatedData['color'] = '#ffffff';
            } else {
                $validatedData['color'] = null;
            }
        }
        $data = [
            "title" => $validatedData["title"],
            "content" => $validatedData["content"],

            // Liste des objets {id, name} à CONSERVER et mettre à jour
            "existing_attachments" => $validatedData["existing_attachments"] ?? [],

            // Objets UploadedFile pour la CRÉATION
            "new_attachments" => $request->file('new_attachments') ?? [],
            "departements" => $validatedData["departements"] ?? [],
        ];
        if(empty($data['color']) && !empty($validatedData['color'])) {
            $data['color'] = $validatedData["color"];
        }

        if(!is_null($validatedData["parent_id"])) {
            $data["folder_id"] = $validatedData["parent_id"];
        }
        try {
            if($id) {
                $document = $this->documentsService->update($id, $data);
                return redirect()->route("navigate.folder", ["folder_id" => $document->folder_id])
                    ->with("success", "Document mis à jour avec success");
            } else {
                $document = $this->documentsService->create($data);
                return redirect()->route("navigate.folder", ["folder_id" => $document->folder_id])
                    ->with("success", "Document créer avec success");
            }

        } catch (BadRequestException $e) {
            // 400 Bad Request (pour une erreur d'argument si non gérée par la validation)
            return redirect()->back()->with(['error' => 'Arguments manquants ou invalides.'. $e->getMessage()]);

        } catch (DocumentNotFoundException|AttachmentNotFoundException|Filesystem\FileNotFoundException) {
            // 404 Not Found (Ressource à mettre à jour non trouvée)
            return redirect()->back()->with(['error' => 'Le document ou un attachement spécifié est introuvable.']);

        } catch (PersistenceException|DiskWriteException $e) {
            // 500 Internal Server Error (Erreur BD ou Disque)
            return redirect()->back()->with(['error' => 'Erreur critique de sauvegarde des données. Veuillez réessayer. ']);

        } catch (AlreadyExistsException $e) {
            // 400 Internal Server Error (Erreur BD ou Disque)
            return redirect()->back()->with(['error' => 'Document / Fichier de ce nom déjà existant']);
        } catch (Throwable $t) {
            // Erreur imprévue (la transaction a été rollback dans le service)
            Log::critical('Unhandled fatal error during document transaction.', ['error' => $t->getMessage(), 'id' => $id]);
            return redirect()->back()->with(['error' => 'Une erreur imprévue est survenue, réessayez plus tard.']);
        }
    }

    public function create($parent_id) {
        if(!$this->foldersService->hasEditAccess($parent_id)) {
            return redirect()->route("navigate.folder", ["folder_id" => $parent_id])->with("warn" , "Vous n'avez pas le droit de modifier ce dossier");
        }
        return \Inertia\Inertia::render('Admin/DocumentForm', [
            "parent_id" => $parent_id,
        ]);
    }

    /**
     * @throws FileNotFoundException
     * @throws DocumentNotFoundException
     */
    public function update($id) {
        $pass = $this->documentsService->hasEditAccess($id);
        $document = $this->documentsService->read($id);
        if(!$pass) {
            return redirect()
                ->route("navigate.folder", ["folder_id" => $document->folder_id])
                ->with(["warn" => "Vous n'avez pas le droit de modifier cette ressource."]);
        }
        try {
            return \Inertia\Inertia::render('Admin/DocumentForm', [
                "document" => $document,
            ]);
        } catch (BadRequestException $e) {
            // 400 Bad Request (pour une erreur d'argument si non gérée par la validation)
            return redirect()->back()->with(['error' => 'Arguments manquants ou invalides.']);
        } catch (DocumentNotFoundException|FileNotFoundException $e) {
            // 404 Not Found
            return redirect()->back()->with(['error' => 'Le document ou un attachement spécifié est introuvable.']);
        }
    }

    public function delete($id) {
        try {
            if(!$this->documentsService->hasEditAccess($id)) {
                return redirect()->route("navigate.folder", ["folder_id" => $this->documentsService->read($id)->folder_id]);
            }
            $this->documentsService->delete($id);
            return redirect()->back()->with("success", "Document deleted successfully");
        } catch (BadRequestException) {
            // 400 Bad Request (pour une erreur d'argument si non gérée par la validation)
            return redirect()->back()->with(['error' => 'Arguments manquants ou invalides.']);

        } catch (DocumentNotFoundException|AttachmentNotFoundException) {
            // 404 Not Found (Ressource à mettre à jour non trouvée)
            return redirect()->back()->with(['error' => 'Le document ou un attachement spécifié est introuvable.']);

        } catch (PersistenceException|DiskWriteException) {
            // 500 Internal Server Error (Erreur BD ou Disque)
            return redirect()->back()->with(['error' => 'Erreur critique de sauvegarde des données. Veuillez réessayer.']);

        } catch (Throwable $t) {
            // Erreur imprévue (la transaction a été rollback dans le service)
            Log::critical('Unhandled fatal error during document transaction.', ['error' => $t->getMessage(), 'id' => $id]);
            return redirect()->back()->with(['error' => 'Une erreur imprévue est survenue, merci de réessayer plus tard.']);
        }
    }
}
