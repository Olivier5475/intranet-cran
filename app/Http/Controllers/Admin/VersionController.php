<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\FilesServiceInterface;
use Illuminate\Support\Facades\Log;
use Throwable;
use Inertia\Inertia;

class VersionController extends Controller
{
    public function __construct(
        private readonly FilesServiceInterface $filesService
    ) {}

    public function restore(int $versionId)
    {
        try {
            // Le service s'occupe de tout : trouver le fichier parent, vérifier le type, etc.
            $this->filesService->restoreFromVersionId($versionId);

            return back()->with('success', 'La version du fichier a été restaurée avec succès.');
        } catch (Throwable $t) {
            Log::error("Échec de la restauration de version", [
                'version_id' => $versionId,
                'error' => $t->getMessage(),
                'trace' => $t->getTraceAsString()
            ]);

            return back()->with('error', "Impossible de restaurer cette version. Le fichier est peut-être corrompu ou manquant.");
        }
    }

    public function fileHistory(int $file_id)
    {
        try {
            return Inertia::render("Admin/Version/File", [
                "versions" => $this->filesService->readVersionsFromParent($file_id),
            ]);
        } catch (Throwable $t) {
            Log::error("Erreur lors de la lecture de l'historique des versions", [
                'file_id' => $file_id,
                'error' => $t->getMessage()
            ]);

            return back()->with('error', "Impossible de charger l'historique des versions.");
        }
    }
}
