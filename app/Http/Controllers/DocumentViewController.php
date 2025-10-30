<?php

namespace App\Http\Controllers;


use App\Services\Interface\DocumentsServiceInterface;

class DocumentViewController {
    public function __invoke($id, DocumentsServiceInterface $documentsService) : \Inertia\Response {
        $document = $documentsService->getDocumentView($id);
        return \Inertia\Inertia::render('DocumentView', [
            "document" => $document
        ]);
    }

}
