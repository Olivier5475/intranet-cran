<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\VersionsServiceInterface;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Throwable;
use Inertia\Inertia;

class VersionController extends Controller
{
    public function __construct(
        private readonly VersionsServiceInterface $versionsService
    ) {}

    public function restore(string $model,int $versionId)
    {
        if($model !== "files" && $model !== "documents"){
            throw new BadRequestHttpException();
        }
        try {
            // Le service s'occupe de tout : trouver le fichier parent, vérifier le type, etc.
            $this->versionsService->restoreFromVersionId($versionId, $model);

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

    public function history(string $model, int $model_id)
    {
        if($model !== "files" && $model !== "documents"){
            throw new BadRequestHttpException();
        }
        try {
            $versions = $this->versionsService->readVersionsFromParent($model_id, $model);
            if($model == "files"){ // Si c'est un fichier
                // On retourne la vue pour les fichiers
                return Inertia::render("Admin/Version/File", [
                    "versions" => $versions,
                ]);
            } else { // Sinon ça veut dire que c'est un document
                // Et on retourne la vue pour un document
                return Inertia::render("Admin/Version/Document", [
                    "versions" => $versions,
                ]);
            }

        } catch (Throwable $t) {
            Log::error("Erreur lors de la lecture de l'historique des versions", [
                'model' => $model,
                'model_id' => $model_id,
                'error' => $t->getMessage()
            ]);

            return back()->with('error', "Impossible de charger l'historique des versions.");
        }
    }
}
