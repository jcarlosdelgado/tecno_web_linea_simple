<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PageVisit;
use Illuminate\Http\Request;

class PageVisitController extends Controller
{
    public function getVisits(Request $request)
    {
        $url = $request->get('url');
        
        if (!$url) {
            return response()->json([
                'error' => 'URL no proporcionada'
            ], 400);
        }

        $totalVisitas = PageVisit::getTotalVisitasPagina($url);
        $visitasHoy = PageVisit::getVisitasHoy($url);
        
        // Obtener el nombre de la página desde la última entrada
        $ultimaVisita = PageVisit::where('page_url', $url)
            ->orderBy('visit_date', 'desc')
            ->first();
        
        $pageName = $ultimaVisita ? $ultimaVisita->page_name : 'Página';

        return response()->json([
            'page_name' => $pageName,
            'page_url' => $url,
            'total_visitas' => $totalVisitas,
            'visitas_hoy' => $visitasHoy,
        ]);
    }
}
