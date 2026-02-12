<?php

namespace App\Http\Controllers;
use App\Services\Interfaces\UserServiceInterface;
use Illuminate\Http\Request;

class AuthController extends Controller {
    public function logout(Request $request, UserServiceInterface $userService)
    {
        // On récupère la page précédente, sinon la racine par défaut
        $referer = $request->headers->get('referer') ?? url('/');

        // On passe cette URL à ton service
        $userService->logout($referer);
    }
}
