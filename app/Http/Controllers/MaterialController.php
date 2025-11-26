<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MaterialController extends Controller
{
    public function index()
    {
        $materiales = Material::with('proveedores')->orderBy('nombre')->get();
        $proveedores = \App\Models\Proveedor::where('activo', true)->orderBy('nombre')->get();

        return Inertia::render('Admin/Inventory/Index', [
            'materiales' => $materiales,
            'proveedores' => $proveedores,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'unidad_medida' => 'required|string|max:20',
            'stock_actual' => 'required|numeric|min:0',
            'stock_minimo' => 'required|numeric|min:0',
            'id_proveedor' => 'nullable|exists:proveedores,id_proveedor',
            'precio_unitario' => 'nullable|numeric|min:0',
        ]);

        $material = Material::create($request->except(['id_proveedor', 'precio_unitario']));

        // If provider is selected, attach it
        if ($request->filled('id_proveedor')) {
            $material->proveedores()->attach($request->id_proveedor, [
                'precio_unitario' => $request->precio_unitario ?? 0,
                'es_principal' => true,
                'creado_en' => now(),
                'actualizado_en' => now(),
            ]);
        }

        return redirect()->back()->with('success', 'Material creado correctamente.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'unidad_medida' => 'required|string|max:20',
            'stock_actual' => 'required|numeric|min:0',
        ]);

        $material = Material::findOrFail($id);
        $material->update($request->except(['id_proveedor', 'precio_unitario']));

        return redirect()->back()->with('success', 'Material actualizado correctamente.');
    }

    public function destroy($id)
    {
        Material::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Material eliminado.');
    }

    public function attachProvider(Request $request, $id)
    {
        $validated = $request->validate([
            'id_proveedor' => 'required|exists:proveedores,id_proveedor',
            'precio_unitario' => 'required|numeric|min:0',
            'es_principal' => 'boolean',
        ]);

        $material = Material::findOrFail($id);

        if ($validated['es_principal'] ?? false) {
            $material->proveedores()->updateExistingPivot(
                $material->proveedores()->where('material_proveedor.id_proveedor', '!=', $validated['id_proveedor'])->pluck('proveedores.id_proveedor'),
                ['es_principal' => false]
            );
        }

        $material->proveedores()->attach($validated['id_proveedor'], [
            'precio_unitario' => $validated['precio_unitario'],
            'es_principal' => $validated['es_principal'] ?? false,
            'creado_en' => now(),
            'actualizado_en' => now(),
        ]);

        return redirect()->back()->with('success', 'Proveedor agregado.');
    }

    public function detachProvider($materialId, $proveedorId)
    {
        Material::findOrFail($materialId)->proveedores()->detach($proveedorId);
        return redirect()->back()->with('success', 'Proveedor removido.');
    }
}
