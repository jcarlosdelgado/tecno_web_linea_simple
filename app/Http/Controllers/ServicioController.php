<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class ServicioController extends Controller
{
    /**
     * Display a listing of active services (public).
     */
    public function index()
    {
        $servicios = Servicio::activo()
            ->ordenado()
            ->get();

        return Inertia::render('Services/Index', [
            'servicios' => $servicios,
        ]);
    }

    /**
     * Show the form for creating a new service (admin only).
     */
    public function create()
    {
        return Inertia::render('Admin/Services/Create');
    }

    /**
     * Store a newly created service in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'precio_base' => 'nullable|numeric|min:0',
            'categoria' => 'nullable|string|max:100',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'activo' => 'boolean',
            'orden' => 'integer|min:0',
        ]);

        // Handle image upload
        if ($request->hasFile('imagen')) {
            $path = $request->file('imagen')->store('servicios', 'public');
            $validated['imagen'] = $path;
        }

        Servicio::create($validated);

        return redirect()->route('admin.servicios.index')
            ->with('success', '✅ Servicio creado exitosamente');
    }

    /**
     * Display the specified service.
     */
    public function show(Servicio $servicio)
    {
        return Inertia::render('Services/Show', [
            'servicio' => $servicio,
        ]);
    }

    /**
     * Show the form for editing the specified service.
     */
    public function edit(Servicio $servicio)
    {
        return Inertia::render('Admin/Services/Edit', [
            'servicio' => $servicio,
        ]);
    }

    /**
     * Update the specified service in storage.
     */
    public function update(Request $request, Servicio $servicio)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'precio_base' => 'nullable|numeric|min:0',
            'categoria' => 'nullable|string|max:100',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'activo' => 'boolean',
            'orden' => 'integer|min:0',
        ]);

        // Handle image upload
        if ($request->hasFile('imagen')) {
            // Delete old image if exists
            if ($servicio->imagen) {
                Storage::disk('public')->delete($servicio->imagen);
            }
            $path = $request->file('imagen')->store('servicios', 'public');
            $validated['imagen'] = $path;
        }

        $servicio->update($validated);

        return redirect()->route('admin.servicios.index')
            ->with('success', '✅ Servicio actualizado exitosamente');
    }

    /**
     * Remove the specified service from storage.
     */
    public function destroy(Servicio $servicio)
    {
        // Delete image if exists
        if ($servicio->imagen) {
            Storage::disk('public')->delete($servicio->imagen);
        }

        $servicio->delete();

        return redirect()->route('admin.servicios.index')
            ->with('success', '🗑️ Servicio eliminado exitosamente');
    }

    /**
     * Admin index - list all services for management.
     */
    public function adminIndex()
    {
        $servicios = Servicio::ordenado()->get();

        return Inertia::render('Admin/Services/Index', [
            'servicios' => $servicios,
        ]);
    }
}
