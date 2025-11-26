<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsCliente
{
    /**
     * Handle an incoming request.
     * Permite acceso a clientes y propietarios (admin)
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if (! $user || (! $user->isCliente() && ! $user->isPropietario())) {
            abort(403, 'Acceso no autorizado. Se requiere rol de Cliente o Propietario.');
        }

        return $next($request);
    }
}
