<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Verifies that the authenticated user has the 'admin' role.
     * Unauthenticated requests are redirected to login.
     * Authenticated non-admin requests receive a 403 Forbidden response.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        if (! $request->user()->isAdmin()) {
            abort(403, 'Acceso restringido al personal de la agencia.');
        }

        return $next($request);
    }
}
