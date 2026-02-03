<?php

namespace App\Http\Controllers;


use App\Exception\DocumentNotFoundException;
use App\Services\Interfaces\DocumentsServiceInterface;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class DocumentViewController {

    public function __invoke(DocumentsServiceInterface $documentsService, $document_id){
        try {
            $document = $documentsService->read($document_id);
        } catch (FileNotFoundException|DocumentNotFoundException $e) {
            return redirect()->back()->with("error", "Document introuvable");
        }

        try {
            return \Inertia\Inertia::render('DocumentView', [
                "document" => $document
            ]);
        } catch (\Throwable $e) {
            return redirect()->back()->with("error", "Document introuvable. " .  $e->getMessage());
        }
    }

}
