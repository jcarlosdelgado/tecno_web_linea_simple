<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permiso;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoleController extends Controller
{
    /**
     * Mostrar lista de roles
     */
    public function index()
    {
        $roles = Role::with('permisos')->withCount('usuarios')->get();
        
        return Inertia::render('Admin/Roles/Index', [
            'roles' => $roles,
        ]);
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        $permisos = Permiso::all()->groupBy('modulo');
        
        return Inertia::render('Admin/Roles/Create', [
            'permisos' => $permisos,
        ]);
    }

    /**
     * Guardar nuevo rol
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:roles,nombre',
            'descripcion' => 'nullable|string',
            'permisos' => 'array',
            'permisos.*' => 'exists:permisos,id_permiso',
        ]);

        $role = Role::create([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? null,
            'activo' => true,
        ]);

        if (isset($validated['permisos'])) {
            $role->permisos()->attach($validated['permisos']);
        }

        return redirect()->route('roles.index')->with('success', 'Rol creado exitosamente');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Role $role)
    {
        $role->load('permisos');
        $permisos = Permiso::all()->groupBy('modulo');
        
        return Inertia::render('Admin/Roles/Edit', [
            'role' => $role,
            'permisos' => $permisos,
            'permisosAsignados' => $role->permisos->pluck('id_permiso'),
        ]);
    }

    /**
     * Actualizar rol
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:roles,nombre,' . $role->id_rol . ',id_rol',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
            'permisos' => 'array',
            'permisos.*' => 'exists:permisos,id_permiso',
        ]);

        $role->update([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? null,
            'activo' => $validated['activo'] ?? true,
        ]);

        if (isset($validated['permisos'])) {
            $role->permisos()->sync($validated['permisos']);
        }

        return redirect()->route('roles.index')->with('success', 'Rol actualizado exitosamente');
    }

    /**
     * Eliminar rol
     */
    public function destroy(Role $role)
    {
        // Verificar que no haya usuarios con este rol
        if ($role->usuarios()->count() > 0) {
            return back()->with('error', 'No se puede eliminar un rol con usuarios asignados');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Rol eliminado exitosamente');
    }

    /**
     * Obtener todos los permisos disponibles
     */
    public function getPermisos()
    {
        $permisos = Permiso::all()->groupBy('modulo');
        
        return response()->json($permisos);
    }

    /**
     * Asignar rol a usuario
     */
    public function assignToUser(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:usuarios,id_usuario',
            'role_id' => 'required|exists:roles,id_rol',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $user->update(['id_rol' => $validated['role_id']]);

        return response()->json(['success' => true, 'message' => 'Rol asignado correctamente']);
    }
}
