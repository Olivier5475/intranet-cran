<?php

namespace App\Http\Controllers;
use App\Services\Interfaces\UserServiceInterface;

class AuthController extends Controller {
    function logout(UserServiceInterface $userService) {
        try {
            $userService->logout();
            return redirect('https://auth.univ-lorraine.fr/logout');
        } catch (\Throwable) {
            return redirect()->back()->with(["error" => "Une erreur est survenu lors de la déconnexion"]);
        }
    }
}
