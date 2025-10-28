<?php

namespace App\Http\Controllers;


class TestFlashController extends Controller {
    function __invoke(): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route("home")
            ->with('success', 'DocumentViewController sauvegardé avec succès !');
    }
}
