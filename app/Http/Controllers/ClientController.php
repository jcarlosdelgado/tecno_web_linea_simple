<?php

namespace App\Http\Controllers;

use App\Models\Trabajo;
use App\Models\Presupuesto;
use App\Models\Servicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ClientController extends Controller
{
    public function dashboard(Request $request)
    {
        $userId = $request->user()->id_usuario;
        
        $trabajos = Trabajo::where('id_cliente', $userId)
            ->with(['servicio', 'presupuestos', 'pagos'])
            ->orderBy('creado_en', 'desc')
            ->get();

        // Calculate statistics
        $stats = [
            'total_trabajos' => $trabajos->count(),
            'trabajos_activos' => $trabajos->where('estado', 'EN_PRODUCCION')->count(),
            'trabajos_completados' => $trabajos->where('estado', 'FINALIZADO')->count(),
            'total_gastado' => $trabajos->sum(function($trabajo) {
                return $trabajo->pagos->where('estado', 'COMPLETADO')->sum('monto');
            }),
        ];

        return Inertia::render('Dashboard', [
            'trabajos' => $trabajos,
            'stats' => $stats,
        ]);
    }

    public function create(Request $request)
    {
        $servicioId = $request->query('servicio');
        $servicio = null;
        
        if ($servicioId) {
            $servicio = Servicio::find($servicioId);
        }
        
        $servicios = Servicio::activo()->ordenado()->get();
        
        return Inertia::render('Client/CreateJob', [
            'servicios' => $servicios,
            'servicioSeleccionado' => $servicio,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_servicio' => 'nullable|exists:servicios,id_servicio',
            'titulo' => 'required|string|max:150',
            'descripcion' => 'required|string',
            'imagenes' => 'nullable|array|max:5',
            'imagenes.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            // New fields
            'medidas' => 'nullable|array',
            'medidas.ancho' => 'nullable|numeric|min:0',
            'medidas.alto' => 'nullable|numeric|min:0',
            'medidas.profundidad' => 'nullable|numeric|min:0',
            'cantidad' => 'nullable|integer|min:1',
            'colores' => 'nullable|string|max:255',
            'fecha_estimada' => 'nullable|date|after:today',
        ]);

        // Handle image uploads
        $imagenesRutas = [];
        if ($request->hasFile('imagenes')) {
            foreach ($request->file('imagenes') as $imagen) {
                $path = $imagen->store('trabajos/referencias', 'public');
                $imagenesRutas[] = $path;
            }
        }

        Trabajo::create([
            'id_cliente' => $request->user()->id_usuario,
            'id_servicio' => $validated['id_servicio'] ?? null,
            'titulo' => $validated['titulo'],
            'descripcion' => $validated['descripcion'],
            'imagenes_referencia' => $imagenesRutas,
            'medidas' => $validated['medidas'] ?? null,
            'cantidad' => $validated['cantidad'] ?? null,
            'colores' => $validated['colores'] ?? null,
            'fecha_estimada' => $validated['fecha_estimada'] ?? null,
            'estado' => 'SOLICITADO',
            'fecha_solicitud' => now(),
        ]);

        return redirect()->route('dashboard')->with('success', 'Trabajo solicitado correctamente.');
    }

    public function show($id)
    {
        $trabajo = Trabajo::with([
            'cliente',
            'servicio',
            'trabajador',
            'presupuestos.detalles.material',
            'pagos.cuotas',
            'seguimientos.fotos'
        ])
            ->where('id_cliente', auth()->user()->id_usuario)
            ->findOrFail($id);

        return Inertia::render('Client/ShowJob', [
            'trabajo' => $trabajo,
        ]);
    }

    public function approveBudget($id)
    {
        $presupuesto = Presupuesto::whereHas('trabajo', function ($query) {
            $query->where('id_cliente', auth()->user()->id_usuario);
        })->findOrFail($id);

        // Update budget status
        $presupuesto->update([
            'estado' => 'APROBADO',
            'fecha_respuesta' => now(),
        ]);

        // Redirect to payment page - Cliente debe pagar PRIMERO
        return redirect()->route('pagos.select', $presupuesto->trabajo->id_trabajo)
            ->with('success', 'Presupuesto aprobado. Por favor, procede con el pago para que iniciemos tu trabajo.');
    }

    public function rejectBudget($id)
    {
        $presupuesto = Presupuesto::whereHas('trabajo', function ($query) {
            $query->where('id_cliente', auth()->user()->id_usuario);
        })->findOrFail($id);

        $presupuesto->update([
            'estado' => 'RECHAZADO',
            'fecha_respuesta' => now(),
        ]);

        return redirect()->back()->with('success', 'Presupuesto rechazado.');
    }

    public function editProfile()
    {
        return Inertia::render('Client/Profile/Edit', [
            'user' => auth()->user()
        ]);
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:usuarios,email,' . auth()->id() . ',id_usuario',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string',
        ]);

        auth()->user()->update($validated);

        return redirect()->back()->with('success', 'Perfil actualizado correctamente.');
    }

    public function history()
    {
        $trabajos = Trabajo::where('id_cliente', auth()->id())
            ->with(['servicio', 'presupuestos', 'pagos'])
            ->orderBy('creado_en', 'desc')
            ->get();

        $stats = [
            'total_trabajos' => $trabajos->count(),
            'total_gastado' => $trabajos->sum(function($t) {
                return $t->pagos->where('estado', 'COMPLETADO')->sum('monto');
            }),
            'promedio_por_trabajo' => $trabajos->count() > 0 
                ? $trabajos->sum(function($t) {
                    return $t->pagos->where('estado', 'COMPLETADO')->sum('monto');
                  }) / $trabajos->count()
                : 0,
            'servicio_mas_solicitado' => $trabajos->groupBy('id_servicio')
                ->sortByDesc(function($group) {
                    return $group->count();
                })
                ->first()?->first()?->servicio?->nombre ?? 'N/A',
        ];

        return Inertia::render('Client/History', [
            'trabajos' => $trabajos,
            'stats' => $stats,
        ]);
    }
}
