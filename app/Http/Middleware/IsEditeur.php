<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsEditeur
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response {
        if (!auth()->check() || !(auth()->user()->role == "admin" || auth()->user()->role == "editeur") ) {
            abort(403, "Accès refusé : vous n'êtes pas administrateur.");
        }
        return $next($request);
    }
}
