<?php

namespace App\Http\Controllers;

use App\Exception\ServerException;
use App\Services\Interfaces\DocumentsServiceInterface;
use Illuminate\Support\Facades\Log;
use Throwable;
use Inertia\Inertia;
use Inertia\Response;

class MainController extends Controller {

    public function __invoke(DocumentsServiceInterface $documentsService): Response
    {
        try {
            $document = $documentsService->readRacineDoc();

            if (!empty($document)) {
                return Inertia::render('DocumentView', [
                    "document" => $document,
                    "folder_id" => 0,
                ]);
            }

            // Cas où la base est vide ou le doc racine n'est pas configuré
            Log::notice("Document racine non trouvé lors de l'accès à l'accueil.");
            return Inertia::render('Erreur/RacineDocNotFound');

        } catch (ServerException $e) {
            Log::error("Erreur serveur lors de la récupération du document racine", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Inertia::render('Erreur/FatalError');

        } catch (Throwable $t) {
            Log::critical("Erreur imprévue sur le MainController", [
                'error' => $t->getMessage(),
                'file' => $t->getFile(),
                'line' => $t->getLine()
            ]);
            return Inertia::render('Erreur/FatalError');
        }
    }
}
