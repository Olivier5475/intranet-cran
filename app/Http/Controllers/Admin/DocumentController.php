<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveDocumentRequest;
use App\Services\Interfaces\{DocumentsServiceInterface, FoldersServiceInterface, UserServiceInterface};
use App\Exception\{AlreadyExistsException, DocumentNotFoundException, PersistenceException};
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Throwable;

class DocumentController extends Controller {

    public function __construct(
        private readonly DocumentsServiceInterface $documentsService,
    ){}

    // --- VUES (GET) ---

    public function create(int $parent_id) {
        // La vérification de droit est maintenant gérée par le Middleware ou la Request,
        // mais on peut garder une sécurité ici si tu préfères.
        return Inertia::render('Admin/DocumentForm', ["parent_id" => $parent_id]);
    }

    public function edit(int $id) {
        try {
            $document = $this->documentsService->read($id);
            return Inertia::render('Admin/DocumentForm', ["document" => $document]);
        } catch (Throwable $t) {
            return redirect()->back()->with('error', 'Impossible de charger le document.');
        }
    }

    // --- ACTIONS (POST) ---

    public function store(SaveDocumentRequest $request) {
        try {
            $data = $request->toServiceData();
            $document = $this->documentsService->create($data);

            return $this->redirectSuccess($document, "créé");
        } catch (Throwable $t) {
            return $this->handleException($t, "création");
        }
    }

    public function update(SaveDocumentRequest $request, int $id) {
        try {
            $data = $request->toServiceData();
            $document = $this->documentsService->update($id, $data);

            return $this->redirectSuccess($document, "mis à jour");
        } catch (Throwable $t) {
            return $this->handleException($t, "mise à jour", $id);
        }
    }

    // --- SUPPRESSION / RESTAURATION ---

    public function delete(int $document_id) {
        try {
            $this->documentsService->delete($document_id);
            return redirect()->back()->with("success", "Le document a été supprimé avec succès.");
        } catch (Throwable $t) {
            return $this->handleException($t, "suppression", $document_id);
        }
    }

    public function archive(int $document_id)
    {
        try {
            $this->documentsService->archive($document_id);
            return redirect()->back()->with("success", "Le document a été archivé avec succès.");
        } catch (Throwable $t) {
            return $this->handleException($t, "suppression", $document_id);
        }
    }

    public function restore(int $id) {
        try {
            $this->documentsService->restore($id);
            return redirect()->back()->with("success", "Le document a été restauré avec succès.");
        } catch (Throwable $t) {
            return $this->handleException($t, "restauration", $id);
        }
    }

    // --- HELPERS PRIVÉS ---

    private function redirectSuccess($document, string $label) {
        $route = empty($document->folder_id)
            ? redirect()->route("navigate.document", ["document_id" => $document->id])
            : redirect()->route("navigate.folder", ["folder_id" => $document->folder_id]);

        return $route->with("success", "Le document a été $label avec succès.");
    }

    private function handleException(Throwable $t, string $action, int $id = null) {
        Log::error("Erreur Document $action", ['id' => $id, 'error' => $t->getMessage()]);

        $message = match(get_class($t)) {
            AlreadyExistsException::class => "Un document ou un fichier portant ce nom existe déjà.",
            default => "Une erreur technique est survenue lors de la $action."
        };

        return redirect()->back()->with('error', $message);
    }
}
