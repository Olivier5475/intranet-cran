<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\Interface\UserServiceInterface;
use Closure;
use Illuminate\Http\Request;
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

        Auth::login($user);
        return $next($request);
    }
}
