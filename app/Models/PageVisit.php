<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PageVisit extends Model
{
    protected $fillable = [
        'page_name',
        'page_url',
        'visit_count',
        'visit_date'
    ];

    protected $casts = [
        'visit_date' => 'date',
    ];

    /**
     * Registrar o incrementar visita para una página
     */
    public static function registrarVisita($pageName, $pageUrl)
    {
        $today = Carbon::today();
        
        $visit = self::firstOrCreate(
            [
                'page_url' => $pageUrl,
                'visit_date' => $today
            ],
            [
                'page_name' => $pageName,
                'visit_count' => 0
            ]
        );
        
        $visit->increment('visit_count');
        
        return $visit;
    }

    /**
     * Obtener total de visitas para una página (todas las fechas)
     */
    public static function getTotalVisitasPagina($pageUrl)
    {
        return self::where('page_url', $pageUrl)->sum('visit_count');
    }

    /**
     * Obtener visitas del día actual
     */
    public static function getVisitasHoy($pageUrl)
    {
        return self::where('page_url', $pageUrl)
            ->where('visit_date', Carbon::today())
            ->value('visit_count') ?? 0;
    }
}
