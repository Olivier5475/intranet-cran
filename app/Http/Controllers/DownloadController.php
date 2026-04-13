<?php

namespace App\Http\Controllers;

use App\Exception\FileNotFoundException;
use App\Services\Interfaces\AttachmentServiceInterface;
use App\Services\Interfaces\FilesServiceInterface;
use Illuminate\Support\Facades\Log;
use Throwable;

class DownloadController extends Controller {
    public function __construct(
        private AttachmentServiceInterface $attachmentService,
        private FilesServiceInterface $filesService,
    ){}

    public function attachment($id){
        try {
            return $this->attachmentService->download($id);
        } catch (Throwable $t) {
            Log::error("Échec du téléchargement de la pièce jointe", [
                'attachment_id' => $id,
                'error' => $t->getMessage()
            ]);
            return redirect()->back()->with("error", "Impossible de télécharger la pièce jointe.");
        }
    }

    public function file($id){
        try {
            return $this->filesService->download($id);
        } catch (Throwable $t) {
            Log::error("Échec du téléchargement du fichier", [
                'file_id' => $id,
                'error' => $t->getMessage()
            ]);
            return redirect()->back()->with("error", "Le téléchargement du fichier a échoué.");
        }
    }

    public function version($id){
        try {
            return $this->filesService->downloadVersion($id);
        } catch (Throwable $t) {
            Log::error("Échec du téléchargement de la version spécifique", [
                'version_id' => $id,
                'error' => $t->getMessage()
            ]);
            return redirect()->back()->with("error", "Impossible de récupérer cette version du fichier.");
        }
    }

    public function preview(int $id)
    {
        if(!$id) {
            abort(400);
        }
        try {
            $file = $this->filesService->read($id);
            return response()->file(storage_path('app/public/'.$file->storage_path));
        } catch (FileNotFoundException) {
            abort(404);
        }
    }
}
