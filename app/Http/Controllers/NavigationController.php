<?php

namespace App\Http\Controllers;

use App\Services\Interface\FoldersServiceInterface;
use Illuminate\Http\Request;

class NavigationController extends Controller
{
    public function __construct(
        private readonly FoldersServiceInterface $foldersService,
    ) {}

    function __invoke(int $folder_id, Request $request, FoldersServiceInterface $foldersService): \Inertia\Response {
        $searchQuery = $request->input('q');

        // UN SEUL APPEL au service pour le contenu
        $items = $this->foldersService->getFolderContents($folder_id, $searchQuery);

        // Un appel pour le fil d'ariane
        $breadcrumbs = $this->foldersService->getBreadcrumbs($folder_id);

        return \Inertia\Inertia::render('Navigation', [
            "parents" => $breadcrumbs,
            "children" => $items,
            "currentSearch" => $searchQuery,
        ]);
    }
}
