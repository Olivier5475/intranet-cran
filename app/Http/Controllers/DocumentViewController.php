<?php

namespace App\Http\Controllers;

use App\Exception\DocumentNotFoundException;
use App\Services\Interfaces\DocumentsServiceInterface;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Log;
use Throwable;
use Inertia\Inertia;

class DocumentViewController extends Controller {

    public function __invoke(DocumentsServiceInterface $documentsService, $document_id) {
        try {
            $document = $documentsService->read($document_id);

            return Inertia::render('DocumentView', [
                "document" => $document
            ]);

        } catch (DocumentNotFoundException|FileNotFoundException $e) {
            // Pas forcément besoin d'un log critique ici, une 404 est souvent une erreur de saisie ou un vieux lien
            return redirect()->back()->with("error", "Le document demandé est introuvable.");

        } catch (Throwable $t) {
            // Ici on logue l'erreur car si le document existe mais que le rendu plante, c'est un bug
            Log::error("Erreur lors de l'affichage du document", [
                'document_id' => $document_id,
                'error' => $t->getMessage(),
                'file' => $t->getFile(),
                'line' => $t->getLine()
            ]);

            return redirect()->back()->with("error", "Une erreur technique est survenue lors de l'affichage du document.");
        }
    }
}
