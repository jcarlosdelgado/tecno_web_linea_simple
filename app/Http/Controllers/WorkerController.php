<?php

namespace App\Http\Controllers;

use App\Models\Trabajo;
use App\Models\SeguimientoTrabajo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class WorkerController extends Controller
{
    public function dashboard()
    {
        $userId = auth()->id();
        
        $trabajos = Trabajo::where('id_trabajador', $userId)
            ->with(['cliente', 'servicio', 'seguimientos'])
            ->orderBy('creado_en', 'desc')
            ->get();

        // Calculate statistics
        $stats = [
            'trabajos_activos' => $trabajos->whereIn('estado', ['EN_PRODUCCION'])->count(),
            'trabajos_completados_mes' => $trabajos->where('estado', 'FINALIZADO')
                ->where('fecha_fin', '>=', now()->startOfMonth())
                ->count(),
            'horas_trabajadas_mes' => $trabajos->sum(function($trabajo) {
                return $trabajo->seguimientos()
                    ->where('creado_en', '>=', now()->startOfMonth())
                    ->sum('horas_trabajadas');
            }),
        ];

        return Inertia::render('Worker/Dashboard', [
            'trabajos' => $trabajos,
            'stats' => $stats,
        ]);
    }

    public function show($id)
    {
        $trabajo = Trabajo::with([
            'cliente',
            'servicio',
            'presupuestos.detalles.material',
            'seguimientos.fotos'
        ])
            ->where('id_trabajador', auth()->id())
            ->findOrFail($id);

        return Inertia::render('Worker/ShowJob', [
            'trabajo' => $trabajo,
        ]);
    }

    public function storeProgress(Request $request, $id)
    {
        $validated = $request->validate([
            'descripcion' => 'required|string',
            'porcentaje_avance' => 'required|integer|min:0|max:100',
            'horas_trabajadas' => 'nullable|numeric|min:0',
            'fotos' => 'nullable|array|max:5',
            'fotos.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $trabajo = Trabajo::where('id_trabajador', auth()->id())
            ->findOrFail($id);

        $seguimiento = $trabajo->seguimientos()->create([
            'descripcion' => $validated['descripcion'],
            'porcentaje_avance' => $validated['porcentaje_avance'],
            'horas_trabajadas' => $validated['horas_trabajadas'] ?? 0,
            'fecha' => now(),
        ]);

        // Handle photo uploads
        if ($request->hasFile('fotos')) {
            foreach ($request->file('fotos') as $foto) {
                $path = $foto->store('trabajos/seguimiento', 'public');
                
                $seguimiento->fotos()->create([
                    'url_foto' => $path,
                    'creado_en' => now(),
                ]);
            }
        }

        // Update job status if completed
        if ($validated['porcentaje_avance'] == 100) {
            $trabajo->update([
                'estado' => 'FINALIZADO',
                'fecha_fin' => now(),
            ]);
        }

        return redirect()->back()->with('success', 'Avance registrado correctamente.');
    }

    public function history()
    {
        $trabajos = Trabajo::where('id_trabajador', auth()->id())
            ->with(['cliente', 'servicio', 'seguimientos'])
            ->where('estado', 'FINALIZADO')
            ->orderBy('fecha_fin', 'desc')
            ->get();

        $stats = [
            'total_completados' => $trabajos->count(),
            'horas_totales' => $trabajos->sum(function($trabajo) {
                return $trabajo->seguimientos->sum('horas_trabajadas');
            }),
            'promedio_tiempo' => $trabajos->count() > 0
                ? $trabajos->sum(function($trabajo) {
                    return $trabajo->seguimientos->sum('horas_trabajadas');
                  }) / $trabajos->count()
                : 0,
        ];

        return Inertia::render('Worker/History', [
            'trabajos' => $trabajos,
            'stats' => $stats,
        ]);
    }
}
