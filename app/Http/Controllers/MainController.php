<?php

namespace App\Http\Controllers;


class MainController extends Controller {
    function __invoke(): \Inertia\Response {
        return \Inertia\Inertia::render('Accueil', []);
    }
}
