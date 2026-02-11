<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\Interfaces\UserServiceInterface;
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
     * @throws \Exception
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

        phpCAS::handleLogoutRequests();
        phpCAS::forceAuthentication();

        $casUser = phpCAS::getUser();
        $attributes = phpCAS::getAttributes();
        $email = $attributes['mail'] ?? ($casUser . '@univ-lorraine.fr');

        if (Auth::check()) {
            if (Auth::user()->email !== $email) {
                Auth::logout();
                $request->session()->invalidate();
            } else {
                return $next($request);
            }
        }

        $user = $this->userService->getUserByEmail($email);

        if (!$user)  {
            try {
                $this->userService->handleUserInDatabase([
                    'nom' => $attributes['sn'] ?? 'Unknown',
                    'prenom' => $attributes['givenname'] ?? $casUser ,
                    'email' => $attributes['mail'] ?? ($casUser . '@univ-lorraine.fr'), // si vous récupérez des attributs
                ]);
            } catch (\Exception) {
                throw new \Exception("Server Error", 500);
            }

            $user = $this->userService->getUserByEmail($attributes['mail']);

            if(!$user) {
                $user = $this->userService->getUserByEmail($casUser . '@univ-lorraine.fr');
            }
        }

        Auth::login($user);
        return $next($request);
    }
}
