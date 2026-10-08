<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // If 'distrito' is checked and user is 'admin', admin has access as well
        if (in_array($user->rol, $roles) || ($user->isAdmin() && in_array('distrito', $roles))) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'No autorizado',
                'message' => 'Su nivel institucional (' . $user->rol_display . ') no tiene permisos para esta acción.',
            ], 403);
        }

        abort(403, 'Acceso restringido: Su rol institucional (' . $user->rol_display . ') no cuenta con autorización para acceder a este módulo o ejecutar esta acción.');
    }
}
