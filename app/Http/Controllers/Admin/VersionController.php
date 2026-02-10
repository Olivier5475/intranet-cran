<?php

namespace App\Http\Controllers\Admin;

use App\Services\Interfaces\FilesServiceInterface;

class VersionController
{

    public function __construct(private readonly FilesServiceInterface $filesService)
    {
    }

    public function restore(int $versionId)
    {
        try {
            // Le service s'occupe de tout : trouver le fichier parent, vérifier le type, etc.
            $this->filesService->restoreFromVersionId($versionId);

            return back()->with('success', 'Version restaurée avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function fileHistory(int $file_id){
        return \Inertia\Inertia::render("Admin/Version/File", [
            "versions" => $this->filesService->readVersionsFromParent($file_id),
        ]);
    }
}
