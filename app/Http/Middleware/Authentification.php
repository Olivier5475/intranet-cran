<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\Interface\UserServiceInterface;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use phpCAS;
use Symfony\Component\HttpFoundation\Response;

class Authentification {
    public function __construct(
        private UserServiceInterface $userService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response {
        // SI ON EST EN LOCAL ALORS ON DEMANDE PAS D'AUTHENTIFICATION
        if (App::environment('local')) {
            if (!Auth::check()) {
                $localDevUser = User::find(1);
                if ($localDevUser) {
                    Auth::login($localDevUser);
                } else {
                    abort(500, "Utilisateur de dev local (ID 1) non trouvé.
                           Veuillez exécuter vos seeders ou en créer un.");
                }
            }
            return $next($request);
        }

        phpCAS::forceAuthentication();
        if (Auth::check()) {
            return $next($request);
        }
        $casUser = phpCAS::getUser();

        $user = $this->userService->getUserByEmail($casUser . '@univ-lorraine.fr');

        if (!$user)  {
            $attributes = phpCAS::getAttributes();

            $this->userService->handleUserInDatabase([
                 'nom' => $attributes['sn'] ?? 'Unknown',
                 'prenom' => $attributes['givenname'] ?? $casUser ,
                 'email' => $attributes['mail'] ?? ($casUser . '@univ-lorraine.fr'), // si vous récupérez des attributs
            ]);
            $user = $this->userService->getUserByEmail($casUser . '@univ-lorraine.fr');
        }
        if($user) {
            Auth::login($user);
        } else {
            var_dump($user);
        }
        return $next($request);
    }
}
