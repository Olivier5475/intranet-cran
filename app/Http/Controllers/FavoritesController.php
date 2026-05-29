<?php

namespace App\Http\Controllers;

use App\Exception\FavoriNotFoundException;
use App\Services\Interfaces\UserServiceInterface;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class FavoritesController extends Controller
{
    // Utilisation de la syntaxe PHP 8 pour déclarer et assigner directement la propriété
    public function __construct(
        private readonly UserServiceInterface $userService,
    ){}

    public function addFavorite(string $ressource_type, int $ressource_id): RedirectResponse
    {
        try {
            $this->userService->addFavorites($ressource_id, $ressource_type);

            return redirect()->back()->with("success", "Ressource ajoutée à vos favoris.");
        } catch (ResourceNotFoundException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Throwable) {
            return redirect()->back()->with('error', 'Erreur lors de l\'ajout de la ressource dans les favoris.');
        }
    }

    public function removeFavorite(string $ressource_type, int $ressource_id): RedirectResponse
    {
        try {
            $this->userService->removeFavorites( $ressource_type, $ressource_id);

            return redirect()->back()->with("success", "Favori supprimé avec succès.");
        } catch (FavoriNotFoundException | AccessDeniedException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Throwable) {
            return redirect()->back()->with('error', 'Erreur lors de la suppression du favori.');
        }
    }
}
