<?php

namespace App\Http\Controllers;


use App\Exception\DocumentNotFoundException;
use App\Services\Interfaces\DocumentsServiceInterface;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class DocumentViewController {

    public function __invoke(DocumentsServiceInterface $documentsService, $folder_id, $id){
        try {
            $document = $documentsService->read($id);
        } catch (FileNotFoundException|DocumentNotFoundException $e) {
            return redirect("/navigation/".$folder_id)->with("error", "Document introuvable");
        }

        try {
            return \Inertia\Inertia::render('DocumentView', [
                "folder_id" => $folder_id,
                "document" => $document
            ]);
        } catch (\Throwable $e) {
            return redirect("/navigation/".$folder_id)->with("error", "Document introuvable. " .  $e->getMessage());
        }
    }

}
