<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProviderController extends Controller
{
    /**
     * Display a listing of providers.
     */
    public function index(Request $request)
    {
        $query = Proveedor::query();

        // Filter by status
        if ($request->filled('activo')) {
            $query->where('activo', $request->activo === 'true');
        }

        // Search by name
        if ($request->filled('search')) {
            $query->where('nombre', 'ILIKE', "%{$request->search}%");
        }

        $proveedores = $query->orderBy('nombre')->paginate(15);

        return Inertia::render('Admin/Providers/Index', [
            'proveedores' => $proveedores,
            'filters' => $request->only(['activo', 'search']),
        ]);
    }

    /**
     * Show the form for creating a new provider.
     */
    public function create()
    {
        return Inertia::render('Admin/Providers/Create');
    }

    /**
     * Store a newly created provider in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'contacto' => 'nullable|string|max:120',
            'telefono' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'direccion' => 'nullable|string|max:255',
            'notas' => 'nullable|string',
        ]);

        $validated['activo'] = true;

        Proveedor::create($validated);

        return redirect()->route('admin.proveedores.index')
            ->with('success', 'Proveedor creado correctamente.');
    }

    /**
     * Show the form for editing the specified provider.
     */
    public function edit($id)
    {
        $proveedor = Proveedor::findOrFail($id);

        return Inertia::render('Admin/Providers/Edit', [
            'proveedor' => $proveedor,
        ]);
    }

    /**
     * Update the specified provider in storage.
     */
    public function update(Request $request, $id)
    {
        $proveedor = Proveedor::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'contacto' => 'nullable|string|max:120',
            'telefono' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'direccion' => 'nullable|string|max:255',
            'notas' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        $proveedor->update($validated);

        return redirect()->route('admin.proveedores.index')
            ->with('success', 'Proveedor actualizado correctamente.');
    }

    /**
     * Remove the specified provider from storage (soft delete).
     */
    public function destroy($id)
    {
        $proveedor = Proveedor::findOrFail($id);

        // Soft delete by marking as inactive
        $proveedor->update(['activo' => false]);

        return redirect()->back()
            ->with('success', 'Proveedor desactivado correctamente.');
    }
}
