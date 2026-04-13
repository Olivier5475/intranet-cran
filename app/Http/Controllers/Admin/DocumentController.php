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
use App\Services\Interfaces\UserServiceInterface;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Throwable;
use Inertia\Inertia;

class DocumentController extends Controller {

    public function __construct(
        private readonly DocumentsServiceInterface $documentsService,
        private readonly FoldersServiceInterface $foldersService,
        private readonly UserServiceInterface $usersService,
    ){}

    public function store(Request $request, $id = null) {
        $validatedData = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'content' => ['sometimes', 'string'],
            'color' => ['nullable', 'string', 'max:16'],
            'existing_attachments' => ['sometimes', 'array'],
            'existing_attachments.*' => ['array'],
            'existing_attachments.*.id' => ['required', 'integer', 'exists:attachments,id'],
            'existing_attachments.*.name' => ['required', 'string', 'max:255'],
            'new_attachments' => ['sometimes', 'array'],
            'new_attachments.*' => ['file', 'max:51200'],
            'departements' => ['sometimes', 'array'],
            'parent_id' => ['integer', 'nullable'],
        ]);

        $data = [];
        // Préparation des données
        if(isset($validatedData["name"])) {
            $data["name"] = $validatedData["name"];
        }
        if(isset($validatedData["departements"])) {
            $data["departements"] = $validatedData["departements"];
        }
        if (isset($validatedData["content"])) {
            $data["content"] = $validatedData["content"];
        }
        if (isset($validatedData["color"])) {
            $data["color"] = $validatedData["color"];
        }
        if(isset($validatedData["existing_attachments"])) {
            $data['existing_attachments'] = $validatedData["existing_attachments"];
        }
        if(isset($validatedData["new_attachments"])) {
             $data["new_attachments"] = $request->file('new_attachments') ?? [];
        }
        if(isset($validatedData["parent_id"])) {
             $data["folder_id"] = $validatedData["parent_id"];
        }

        try {
            if($id) {
                $document = $this->documentsService->update($id, $data);
                if(empty($document->folder_id)) {
                    return redirect()->route("navigate.document", ["document_id" => $document->id])
                        ->with("success", "Le document a été modifier avec succès");
                }
                return redirect()->route("navigate.folder", ["folder_id" => $document->folder_id])
                    ->with("success", "Le document a été mis à jour avec succès.");
            } else {
                $document = $this->documentsService->create($data);
                if(empty($document->folder_id)) {
                    return redirect()->route("navigate.document", ["document_id" => $document->id])
                        ->with("success", "Le document a été modifier avec succès");
                }
                return redirect()->route("navigate.folder", ["folder_id" => $document->folder_id])
                    ->with("success", "Le document a été créer avec succès.");
            }

        } catch (BadRequestException $e) {
            Log::warning("Requête malformée lors de l'enregistrement du document", ['id' => $id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Les données envoyées sont incorrectes.');

        } catch (DocumentNotFoundException|AttachmentNotFoundException|FileNotFoundException $e) {
            Log::error("Ressource introuvable lors de l'enregistrement", ['id' => $id, 'type' => get_class($e)]);
            return redirect()->back()->with('error', 'Le document ou l’une de ses pièces jointes est introuvable.');

        } catch (PersistenceException|DiskWriteException $e) {
            Log::error("Erreur de stockage lors de l'enregistrement du document", [
                'id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString() ?? 'N/A'
            ]);
            return redirect()->back()->with('error', 'Une erreur technique est survenue lors de l’écriture des données.');

        } catch (AlreadyExistsException $e) {
            return redirect()->back()->with('error', 'Un document ou un fichier portant ce nom existe déjà.');

        } catch (Throwable $t) {
            Log::critical('Erreur fatale imprévue (Document Store)', [
                'id' => $id,
                'error' => $t->getMessage()
            ]);
            return redirect()->back()->with('error', 'Une erreur imprévue est survenue. Veuillez réessayer plus tard.');
        }
    }

    public function create($parent_id) {
        if($parent_id != 0 && !$this->foldersService->hasEditAccess($parent_id)) {
            Log::notice("Tentative de création non autorisée dans un dossier", ['folder_id' => $parent_id, 'user_id' => auth()->id()]);
            return redirect()->route("navigate.folder", ["folder_id" => $parent_id])
                ->with("error" , "Vous n'avez pas les permissions nécessaires pour créer un document ici.");
        }
        elseif ($parent_id == 0 && !$this->usersService->isAdmin()) {
            return redirect()->back()->with("error", "Seul un administrateur peut créer un document à la racine.");
        }

        return Inertia::render('Admin/DocumentForm', ["parent_id" => $parent_id]);
    }

    public function update($id) {
        try {
            if(!$this->documentsService->hasEditAccess($id)) {
                $document = $this->documentsService->read($id);
                Log::notice("Accès refusé en modification de document", ['document_id' => $id, 'user_id' => auth()->id()]);
                return redirect()->route("navigate.folder", ["folder_id" => $document->folder_id])
                    ->with("error", "Vous n'avez pas le droit de modifier ce document.");
            }

            $document = $this->documentsService->read($id);
            return Inertia::render('Admin/DocumentForm', ["document" => $document]);

        } catch (DocumentNotFoundException|FileNotFoundException $e) {
            Log::error("Tentative de modification d'un document inexistant", ['id' => $id]);
            return redirect()->back()->with('error', 'Le document que vous souhaitez modifier est introuvable.');
        } catch (Throwable $t) {
            Log::error("Erreur lors du chargement du formulaire d'édition", ['id' => $id, 'error' => $t->getMessage()]);
            return redirect()->back()->with('error', 'Impossible de charger le document.');
        }
    }

    public function delete($id) {
        try {
            if(!$this->documentsService->hasEditAccess($id)) {
                Log::notice("Tentative de suppression non autorisée", ['id' => $id, 'user_id' => auth()->id()]);
                return redirect()->back()->with("error", "Vous n'avez pas les permissions pour supprimer ce document.");
            }

            $this->documentsService->delete($id);
            return redirect()->back()->with("success", "Le document a été supprimé avec succès.");

        } catch (DocumentNotFoundException|AttachmentNotFoundException $e) {
            return redirect()->back()->with('error', 'Le document est déjà supprimé ou introuvable.');

        } catch (PersistenceException|DiskWriteException $e) {
            Log::error("Erreur lors de la suppression physique/logique du document", ['id' => $id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Erreur lors de la suppression du fichier sur le serveur.');

        } catch (Throwable $t) {
            Log::critical('Crash lors de la suppression du document', ['id' => $id, 'error' => $t->getMessage()]);
            return redirect()->back()->with('error', 'Une erreur imprévue est survenue.');
        }
    }

    public function restore(int $document_id)
    {
        try {
            if(!$this->documentsService->hasEditAccess($document_id)) {
                Log::notice("Tentative de suppression non autorisée", ['id' => $document_id, 'user_id' => auth()->id()]);
                return redirect()->back()->with("error", "Vous n'avez pas les permissions pour supprimer ce document.");
            }

            $this->documentsService->restore($document_id);
            return redirect()->back()->with("success", "Le document a été supprimé avec succès.");

        } catch (DocumentNotFoundException|AttachmentNotFoundException $e) {
            return redirect()->back()->with('error', 'Le document est déjà supprimé ou introuvable.');

        } catch (PersistenceException|DiskWriteException $e) {
            Log::error("Erreur lors de la suppression physique/logique du document", ['id' => $document_id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Erreur lors de la suppression du fichier sur le serveur.');

        } catch (Throwable $t) {
            Log::critical('Crash lors de la suppression du document', ['id' => $document_id, 'error' => $t->getMessage()]);
            return redirect()->back()->with('error', 'Une erreur imprévue est survenue.');
        }
    }
}
