<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\FoldersServiceInterface;
use Illuminate\Http\Request;

class NavigationController extends Controller
{
    public function __construct(
        private readonly FoldersServiceInterface $foldersService,
    ) {}

    function __invoke(int $folder_id, Request $request, FoldersServiceInterface $foldersService) {
        if($folder_id === 0) {
            return redirect()->route("home");
        }
        $searchQuery = $request->input('q');

        $items = $this->foldersService->getFolderContents($folder_id, $searchQuery);

        try {
            $breadcrumbs = $this->foldersService->getBreadcrumbs($folder_id);
        } catch (\Throwable) {
            return redirect()->route("home")->with("success", "Erreur chargement des dossiers");
        }

        return \Inertia\Inertia::render('Navigation', [
            "parents" => $breadcrumbs,
            "children" => $items,
            "currentSearch" => $searchQuery,
        ]);
    }
}
