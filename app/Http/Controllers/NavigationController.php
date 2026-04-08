<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\FoldersServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;
use Inertia\Inertia;

class NavigationController extends Controller
{
    public function __construct(
        private readonly FoldersServiceInterface $foldersService,
    ) {}

    public function __invoke(int $folder_id, Request $request)
    {
        // Redirection vers l'accueil si on tente d'accéder à la racine via l'ID 0
        if ($folder_id === 0) {
            return redirect()->route("home");
        }

        $searchQuery = $request->input('q');
        $isArchived = $request->routeIs('navigate.archived');

        try {
            // Récupération du contenu
            $items = $this->foldersService->getFolderContents($folder_id, $searchQuery, $isArchived);
            // Récupération du fil d'Ariane
            $breadcrumbs = $this->foldersService->getBreadcrumbs($folder_id);

            return Inertia::render('Navigation', [
                "parents" => $breadcrumbs,
                "children" => $items,
                "currentSearch" => $searchQuery,
                "isArchived" => $isArchived,
            ]);

        } catch (Throwable $t) {
            Log::error("Erreur de navigation dans le dossier", [
                'folder_id' => $folder_id,
                'search_query' => $searchQuery,
                'error' => $t->getMessage(),
                'trace' => $t->getTraceAsString()
            ]);

            return redirect()->route("home")->with("error", "Impossible d'accéder au dossier demandé ou de charger son contenu.");
        }
    }
}
