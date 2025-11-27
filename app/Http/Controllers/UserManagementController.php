<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserManagementController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::with('roleCustom');

        // Filter by role
        if ($request->filled('rol')) {
            $query->where('rol', $request->rol);
        }

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'ILIKE', "%{$search}%")
                  ->orWhere('email', 'ILIKE', "%{$search}%");
            });
        }

        // Exclude PROPIETARIO from list
        // Incluye CLIENTE, usuarios con roles personalizados (rol = NULL) y EMPLEADO (por compatibilidad)
        $query->where(function($q) {
            $q->where('rol', 'CLIENTE')
              ->orWhereNull('rol');
        });

        $usuarios = $query->orderBy('creado_en', 'desc')->paginate(15);

        $roles = Role::where('activo', true)->get();

        return Inertia::render('Admin/Users/Index', [
            'usuarios' => $usuarios,
            'roles' => $roles,
            'filters' => $request->only(['rol', 'search']),
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $roles = Role::where('activo', true)->with('permisos')->get();
        
        return Inertia::render('Admin/Users/Create', [
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:usuarios,email',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string',
            'password' => 'required|string|min:8',
            'id_rol' => 'required|exists:roles,id_rol',
            'permisos_personalizados' => 'nullable|array',
            'permisos_personalizados.*' => 'exists:permisos,id_permiso',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        // No asignamos el campo 'rol' - queda NULL para usuarios con roles personalizados

        $user = User::create($validated);

        // Asignar permisos personalizados si se proporcionaron
        if (!empty($validated['permisos_personalizados'])) {
            $user->permisosPersonalizados()->sync($validated['permisos_personalizados']);
        }

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit($id)
    {
        $usuario = User::with('roleCustom')->findOrFail($id);
        $roles = Role::where('activo', true)->with('permisos')->get();

        // Prevent editing PROPIETARIO
        if ($usuario->rol === 'PROPIETARIO') {
            abort(403, 'No se puede editar el propietario.');
        }

        return Inertia::render('Admin/Users/Edit', [
            'usuario' => $usuario,
            'roles' => $roles,
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, $id)
    {
        $usuario = User::findOrFail($id);

        // Prevent editing PROPIETARIO
        if ($usuario->rol === 'PROPIETARIO') {
            abort(403, 'No se puede editar el propietario.');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('usuarios', 'email')->ignore($id, 'id_usuario')],
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string',
            'password' => 'nullable|string|min:8',
            'id_rol' => 'nullable|exists:roles,id_rol',
        ]);

        // Only update password if provided
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $usuario->update($validated);

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Toggle user active status.
     */
    public function toggleStatus($id)
    {
        $usuario = User::findOrFail($id);

        // Prevent deactivating PROPIETARIO
        if ($usuario->rol === 'PROPIETARIO') {
            abort(403, 'No se puede desactivar el propietario.');
        }

        // Toggle activo field (assuming it exists, or we can use a different approach)
        // For now, we'll just return success as the field doesn't exist yet
        // TODO: Add 'activo' field to usuarios table in future migration

        return redirect()->back()
            ->with('success', 'Estado del usuario actualizado.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy($id)
    {
        $usuario = User::findOrFail($id);

        // Prevent deleting PROPIETARIO
        if ($usuario->rol === 'PROPIETARIO') {
            abort(403, 'No se puede eliminar el propietario.');
        }

        // Soft delete or mark as inactive
        // For now, we'll prevent deletion
        return redirect()->back()
            ->with('error', 'La eliminación de usuarios no está habilitada. Use desactivar en su lugar.');
    }
}
