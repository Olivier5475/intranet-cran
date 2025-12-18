<?php

namespace App\Http\Controllers\Admin;

use App\Exception\AttachmentNotFoundException;
use App\Exception\DiskWriteException;
use App\Exception\DocumentNotFoundException;
use App\Exception\PersistenceException;
use App\Http\Controllers\Controller;
use App\Services\Interfaces\DocumentsServiceInterface;
use App\Services\Interfaces\UserServiceInterface;
use Illuminate\Contracts\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Throwable;

class DocumentController extends Controller {
    public function __construct(
        private readonly DocumentsServiceInterface $documentsService,
        private readonly UserServiceInterface $userService,
    ){}

    public function store(Request $request, $folder_id, $id = null) {
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
        ];
        if(empty($data['color']) && !empty($validatedData['color'])) {
            $data['color'] = $validatedData["color"];
        }

        if(!empty($folder_id)) {
            $data["folder_id"] = $folder_id;
        }

        try {
            if($id) {
                $this->documentsService->update($id, $data);
                return redirect()->route("home")
                    ->with("success", "Document updated successfully");
            } else {
                $this->documentsService->create($data);
                return redirect()->route("home")
                    ->with("success", "Document created successfully");
            }

        } catch (BadRequestException $e) {
            // 400 Bad Request (pour une erreur d'argument si non gérée par la validation)
            return redirect()->back()->with(['success' => 'Arguments manquants ou invalides.'. $e->getMessage()]);

        } catch (DocumentNotFoundException|AttachmentNotFoundException|Filesystem\FileNotFoundException) {
            // 404 Not Found (Ressource à mettre à jour non trouvée)
            return redirect()->back()->with(['success' => 'Le document ou un attachement spécifié est introuvable.']);

        } catch (PersistenceException|DiskWriteException $e) {
            // 500 Internal Server Error (Erreur BD ou Disque)
            return redirect()->back()->with(['success' => 'Erreur critique de sauvegarde des données. Veuillez réessayer. '. $e->getMessage()]);

        } catch (Throwable $t) {
            // Erreur imprévue (la transaction a été rollback dans le service)
            Log::critical('Unhandled fatal error during document transaction.', ['error' => $t->getMessage(), 'id' => $id]);
            return redirect()->back()->with(['success' => 'Une erreur imprévue est survenue (Code: 500).']);
        }
    }

    public function create($folder_id) {
        return \Inertia\Inertia::render('Admin/DocumentForm', [
            "folder_id" => $folder_id,
            "role" => $this->userService->getRole(),
        ]);
    }
    public function update($folder_id, $id) {
        try {
            return \Inertia\Inertia::render('Admin/DocumentForm', [
                "document" => $this->documentsService->read($id),
                "folder_id" => $folder_id,
                "role" => $this->userService->getRole(),
            ]);
        } catch (BadRequestException $e) {
            // 400 Bad Request (pour une erreur d'argument si non gérée par la validation)
            return redirect()->back()->with(['error' => 'Arguments manquants ou invalides.']);
        } catch (DocumentNotFoundException|Filesystem\FileNotFoundException $e) {
            // 404 Not Found
            return redirect()->back()->with(['error' => 'Le document ou un attachement spécifié est introuvable.']);
        }
    }

    public function delete($folder_id, $id) {
        try {
            $this->documentsService->delete($folder_id, $id);
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
            return redirect()->back()->with(['error' => 'Une erreur imprévue est survenue (Code: 500).']);
        }
    }
}
