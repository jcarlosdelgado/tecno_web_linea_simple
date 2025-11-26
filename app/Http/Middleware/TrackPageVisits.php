<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\PageVisit;
use Illuminate\Support\Facades\Route;

class TrackPageVisits
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Solo rastrear páginas GET y que sean exitosas
        if ($request->isMethod('GET') && $response->getStatusCode() === 200) {
            try {
                $routeName = Route::currentRouteName();
                $url = $request->path();
                
                // Obtener nombre amigable de la página
                $pageName = $this->getPageName($routeName, $url);
                
                // Registrar la visita
                PageVisit::registrarVisita($pageName, $url);
            } catch (\Exception $e) {
                // Si falla, no interrumpir la aplicación
                logger()->error('Error al registrar visita: ' . $e->getMessage());
            }
        }
        
        return $response;
    }

    /**
     * Obtener nombre amigable para la página
     */
    private function getPageName($routeName, $url)
    {
        $nombres = [
            'dashboard' => 'Dashboard',
            'admin.dashboard' => 'Dashboard Admin',
            'trabajos.index' => 'Trabajos',
            'trabajos.create' => 'Crear Trabajo',
            'servicios.index' => 'Servicios',
            'admin.proveedores.index' => 'Proveedores',
            'admin.materiales.index' => 'Materiales',
            'admin.gastos.index' => 'Gastos',
            'admin.usuarios.index' => 'Usuarios',
            'admin.roles.index' => 'Roles',
            'admin.pagos.index' => 'Pagos',
            'admin.reports.clients' => 'Reporte Clientes',
            'admin.reports.profitability' => 'Reporte Rentabilidad',
            'admin.reports.inventory' => 'Reporte Inventario',
            'pagos.historial' => 'Historial de Pagos',
        ];
        
        return $nombres[$routeName] ?? ucfirst(str_replace(['/', '-', '_'], ' ', $url));
    }
}
