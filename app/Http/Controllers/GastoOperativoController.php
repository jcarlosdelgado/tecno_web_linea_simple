<?php

namespace App\Http\Controllers;

use App\Models\GastoOperativo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class GastoOperativoController extends Controller
{
    /**
     * Lista de gastos operativos con filtros
     */
    public function index(Request $request)
    {
        $query = GastoOperativo::with('registrador')
            ->orderBy('fecha', 'desc');

        // Filtro por fecha
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->fecha_hasta);
        }

        // Filtro por categoría
        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }

        $gastos = $query->paginate(20);

        // Calcular estadísticas
        $stats = [
            'total_mes_actual' => GastoOperativo::whereMonth('fecha', now()->month)
                ->whereYear('fecha', now()->year)
                ->sum('monto'),
            'total_dia' => GastoOperativo::whereDate('fecha', now()->toDateString())
                ->sum('monto'),
            'total_general' => GastoOperativo::sum('monto'),
        ];

        // Categorías disponibles
        $categorias = GastoOperativo::select('categoria')
            ->distinct()
            ->pluck('categoria');

        return Inertia::render('Admin/GastosOperativos/Index', [
            'gastos' => $gastos,
            'stats' => $stats,
            'categorias' => $categorias,
            'filtros' => $request->only(['fecha_desde', 'fecha_hasta', 'categoria']),
        ]);
    }

    /**
     * Formulario de creación
     */
    public function create()
    {
        return Inertia::render('Admin/GastosOperativos/Create');
    }

    /**
     * Guardar nuevo gasto
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'categoria' => 'required|string|max:100',
            'descripcion' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0',
            'fecha' => 'required|date',
            'comprobante' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $validated['registrado_por'] = auth()->id();

        // Subir comprobante si existe
        if ($request->hasFile('comprobante')) {
            $validated['comprobante'] = $request->file('comprobante')->store('comprobantes/gastos', 'public');
        }

        GastoOperativo::create($validated);

        return redirect()->route('admin.gastos-operativos.index')
            ->with('success', 'Gasto operativo registrado exitosamente.');
    }

    /**
     * Formulario de edición
     */
    public function edit(GastoOperativo $gastoOperativo)
    {
        return Inertia::render('Admin/GastosOperativos/Edit', [
            'gasto' => $gastoOperativo->load('registrador'),
        ]);
    }

    /**
     * Actualizar gasto
     */
    public function update(Request $request, GastoOperativo $gastoOperativo)
    {
        $validated = $request->validate([
            'categoria' => 'required|string|max:100',
            'descripcion' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0',
            'fecha' => 'required|date',
            'comprobante' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        // Subir nuevo comprobante si existe
        if ($request->hasFile('comprobante')) {
            // Eliminar comprobante anterior
            if ($gastoOperativo->comprobante) {
                Storage::disk('public')->delete($gastoOperativo->comprobante);
            }
            $validated['comprobante'] = $request->file('comprobante')->store('comprobantes/gastos', 'public');
        }

        $gastoOperativo->update($validated);

        return redirect()->route('admin.gastos-operativos.index')
            ->with('success', 'Gasto operativo actualizado exitosamente.');
    }

    /**
     * Eliminar gasto
     */
    public function destroy(GastoOperativo $gastoOperativo)
    {
        // Eliminar comprobante si existe
        if ($gastoOperativo->comprobante) {
            Storage::disk('public')->delete($gastoOperativo->comprobante);
        }

        $gastoOperativo->delete();

        return redirect()->route('admin.gastos-operativos.index')
            ->with('success', 'Gasto operativo eliminado exitosamente.');
    }

    /**
     * Reporte de gastos por categoría
     */
    public function reportePorCategoria(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month');

        $query = GastoOperativo::selectRaw('categoria, SUM(monto) as total')
            ->whereYear('fecha', $year);

        if ($month) {
            $query->whereMonth('fecha', $month);
        }

        $gastosPorCategoria = $query->groupBy('categoria')
            ->orderBy('total', 'desc')
            ->get();

        return response()->json([
            'gastos_por_categoria' => $gastosPorCategoria,
            'total' => $gastosPorCategoria->sum('total'),
        ]);
    }
}
