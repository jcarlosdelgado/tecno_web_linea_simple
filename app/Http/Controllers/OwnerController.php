<?php

namespace App\Http\Controllers;

use App\Models\Trabajo;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OwnerController extends Controller
{
    public function dashboard()
    {
        $trabajos = Trabajo::with('cliente')
            ->orderBy('creado_en', 'desc')
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'trabajos' => $trabajos,
        ]);
    }

    public function show($id)
    {
        $trabajo = Trabajo::with(['cliente', 'presupuestos.detalles.material', 'pagos', 'seguimientos.fotos'])
            ->findOrFail($id);
        
        // Get all materials for budget creation
        $materiales = \App\Models\Material::orderBy('nombre', 'asc')->get();

        return Inertia::render('Admin/ShowJob', [
            'trabajo' => $trabajo,
            'materiales' => $materiales,
        ]);
    }

    public function storeBudget(Request $request, $id)
    {
        $request->validate([
            'materiales' => 'required|array|min:1',
            'materiales.*.id_material' => 'required|exists:materiales,id_material',
            'materiales.*.cantidad' => 'required|numeric|min:0.01',
            'mano_obra' => 'required|numeric|min:0',
            'otros_costos' => 'nullable|numeric|min:0',
            'notas' => 'nullable|string',
            'notas_adicionales' => 'nullable|string',
        ]);

        $trabajo = Trabajo::findOrFail($id);
        
        // Check stock availability
        $inventoryService = new \App\Services\InventoryService();
        $insufficient = $inventoryService->checkMultipleStock($request->materiales);
        
        if (!empty($insufficient)) {
            return redirect()->back()->withErrors([
                'materiales' => 'Stock insuficiente para algunos materiales: ' . 
                    collect($insufficient)->pluck('nombre')->implode(', ')
            ])->withInput();
        }

        // Calculate materials cost
        $totalMateriales = $inventoryService->calculateMaterialsCost($request->materiales);
        $manoObra = $request->mano_obra;
        $otrosCostos = $request->otros_costos ?? 0;
        $montoTotal = $totalMateriales + $manoObra + $otrosCostos;

        // Create budget
        $presupuesto = $trabajo->presupuestos()->create([
            'monto_total' => $montoTotal,
            'mano_obra' => $manoObra,
            'otros_costos' => $otrosCostos,
            'estado' => 'PENDIENTE',
            'fecha_emision' => now(),
            'fecha_validez' => now()->addDays(30),
            'notas' => $request->notas,
            'notas_adicionales' => $request->notas_adicionales,
        ]);

        // Create budget details for each material
        foreach ($request->materiales as $material) {
            $materialModel = \App\Models\Material::find($material['id_material']);
            
            $presupuesto->detalles()->create([
                'id_material' => $material['id_material'],
                'cantidad' => $material['cantidad'],
                'precio_unitario' => $materialModel->precio_unitario,
                'subtotal' => $materialModel->precio_unitario * $material['cantidad'],
            ]);
        }

        // Update job status
        $trabajo->update(['estado' => 'PRESUPUESTADO']);

        return redirect()->back()->with('success', 'Presupuesto creado y enviado al cliente.');
    }

    public function storeTracking(Request $request, $id)
    {
        $request->validate([
            'descripcion' => 'required|string',
            'porcentaje_avance' => 'required|integer|min:0|max:100',
            'horas_trabajadas' => 'nullable|numeric|min:0',
            'fotos' => 'nullable|array|max:5',
            'fotos.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $trabajo = Trabajo::findOrFail($id);

        $seguimiento = $trabajo->seguimientos()->create([
            'descripcion' => $request->descripcion,
            'porcentaje_avance' => $request->porcentaje_avance,
            'horas_trabajadas' => $request->horas_trabajadas ?? 0,
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
        if ($request->porcentaje_avance == 100) {
            $trabajo->update([
                'estado' => 'FINALIZADO',
                'fecha_fin' => now(),
            ]);
        }

        return redirect()->back()->with('success', 'Avance registrado correctamente.');
    }

    /**
     * Assign a worker to a job.
     */
    public function assignWorker(Request $request, $id)
    {
        $validated = $request->validate([
            'id_trabajador' => 'required|exists:usuarios,id_usuario'
        ]);

        $trabajo = Trabajo::findOrFail($id);
        
        // Verify the user is actually a worker
        $trabajador = \App\Models\User::findOrFail($validated['id_trabajador']);
        if ($trabajador->rol !== 'TRABAJADOR') {
            return redirect()->back()->withErrors([
                'id_trabajador' => 'El usuario seleccionado no es un trabajador.'
            ]);
        }

        $trabajo->update([
            'id_trabajador' => $validated['id_trabajador']
        ]);

        return redirect()->back()->with('success', 'Trabajador asignado correctamente.');
    }
}
