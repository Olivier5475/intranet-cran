<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\UserServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class AuthController extends Controller {

    public function logout(Request $request, UserServiceInterface $userService)
    {
        try {
            // On récupère l'identifiant avant la déconnexion pour le log
            $userId = auth()->id();

            // On récupère la page précédente, sinon la racine par défaut
            $referer = $request->headers->get('referer') ?? url('/');

            // On passe cette URL à ton service
            $userService->logout($referer);

            Log::info("Utilisateur déconnecté", ['user_id' => $userId]);

        } catch (Throwable $t) {
            Log::error("Erreur lors de la déconnexion", [
                'user_id' => auth()->id(),
                'error' => $t->getMessage()
            ]);

            // En cas d'erreur, on redirige quand même vers l'accueil pour ne pas bloquer l'user
            return redirect('/')->with('error', 'Une erreur est survenue lors de la déconnexion.');
        }
    }
}
