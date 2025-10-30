<?php

namespace App\Http\Controllers;


use App\Services\Interface\FoldersServiceInterface;

class NavigationController extends Controller {
    function __invoke(int $folder_id, FoldersServiceInterface $foldersService): \Inertia\Response {
        // on récupère le chemin d'accès du dossier courrant
        $parents = $foldersService->getBreadcrumbs($folder_id);

        // on récupère les enfants (dossier, fichiers et documents) à partir du dossier courant
        $children = $foldersService->getChildren($folder_id);

        return \Inertia\Inertia::render('Navigation', [
            "parents" => $parents,
            "children" => $children
        ]);
    }
}
