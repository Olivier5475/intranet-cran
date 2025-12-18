<?php

namespace App\Http\Controllers;


use App\Exception\DocumentNotFoundException;
use App\Exception\ServerException;
use App\Services\Interfaces\DocumentsServiceInterface;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class MainController extends Controller {
    function __invoke(DocumentsServiceInterface $documentsService): \Inertia\Response {
        try {
            $document = $documentsService->readRacineDoc();
            if(!empty($document)) {
                return \Inertia\Inertia::render('DocumentView', [
                    "document" => $document,
                    "folder_id" => 0,
                ]);
            }
            return \Inertia\Inertia::render('Erreur/RacineDocNotFound');
        } catch (ServerException $e) {
            return \Inertia\Inertia::render('Erreur/FatalError');
        }
    }
}
