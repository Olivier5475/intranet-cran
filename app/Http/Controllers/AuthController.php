<?php

namespace App\Http\Controllers;
use App\Services\Interfaces\UserServiceInterface;

class AuthController extends Controller {
    function logout(UserServiceInterface $userService) {
        $userService->logout();
    }
}
