<?php

namespace App\Http\Controllers;

use App\Exception\DocumentNotFoundException;
use App\Exception\VersionNotFoundException;
use App\Services\Interfaces\DocumentsServiceInterface;
use App\Services\Interfaces\FoldersServiceInterface;
use App\Services\Interfaces\VersionsServiceInterface;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Log;
use Throwable;
use Inertia\Inertia;

class DocumentViewController extends Controller {

    public function index(
        DocumentsServiceInterface $documentsService,
        FoldersServiceInterface $foldersService,
        $document_id) {
        try {
            $document = $documentsService->read($document_id);
            $breadcrumbs = $foldersService->getBreadcrumbs($document->folder_id);
            return Inertia::render('DocumentView', [
                "document" => $document,
                "parents" => $breadcrumbs,
            ]);

        } catch (DocumentNotFoundException|FileNotFoundException $e) {
            // Pas forcément besoin d'un log critique ici, une 404 est souvent une erreur de saisie ou un vieux lien
            return redirect()->back()->with("error", "Le document demandé est introuvable.");

        } catch (Throwable $t) {
            // Ici, on logue l'erreur, car si le document existe, mais que le rendu plante, c'est un bug.
            Log::error("Erreur lors de l'affichage du document", [
                'document_id' => $document_id,
                'error' => $t->getMessage(),
                'file' => $t->getFile(),
                'line' => $t->getLine()
            ]);

            return redirect()->back()->with("error", "Une erreur technique est survenue lors de l'affichage du document.");
        }
    }

    public function version(
        VersionsServiceInterface $versionsService,
        $version_id,
    ) {
        try {
            $document = $versionsService->readDocumentVersionFromId($version_id);
            return Inertia::render('DocumentView', [
                "document" => $document,
                "versionId" => $version_id
            ]);

        } catch (VersionNotFoundException $e) {
            // Pas forcément besoin d'un log critique ici, une 404 est souvent une erreur de saisie ou un vieux lien
            return redirect()->back()->with("error", "Le document demandé est introuvable.");

        } catch (Throwable $t) {
            // Ici, on logue l'erreur, car si le document existe, mais que le rendu plante, c'est un bug
            Log::error("Erreur lors de l'affichage du document", [
                'version_id' => $version_id,
                'error' => $t->getMessage(),
                'file' => $t->getFile(),
                'line' => $t->getLine()
            ]);

            return redirect()->back()->with("error", "Une erreur technique est survenue lors de l'affichage du document.");
        }
    }
}
