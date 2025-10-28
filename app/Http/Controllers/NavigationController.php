<?php

namespace App\Http\Controllers;


use App\Interface\FoldersServiceInterface;
use Illuminate\Http\Request;

class NavigationController extends Controller {
    function __invoke(int $folder_id, FoldersServiceInterface $foldersService): \Inertia\Response {
        // on récupère le chemin d'accès
        $parents = $foldersService->getBreadcrumbs($folder_id);

        // on récupère les dossiers enfants à partir du path courant
        $children = $foldersService->getChildren($folder_id);

        return \Inertia\Inertia::render('Navigation', [
            "parents" => $parents,
            "children" => $children
        ]);
    }
}
