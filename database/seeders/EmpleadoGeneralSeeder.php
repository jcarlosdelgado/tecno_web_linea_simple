<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permiso;

class EmpleadoGeneralSeeder extends Seeder
{
    public function run(): void
    {
        $empleado = Role::create([
            'nombre' => 'Empleado General',
            'descripcion' => 'Empleado con permisos básicos del sistema',
            'activo' => true,
        ]);
        
        $permisos = Permiso::whereIn('nombre', [
            'ver_trabajos',
            'ver_presupuestos',
            'ver_servicios',
            'ver_seguimiento',
        ])->pluck('id_permiso');
        
        $empleado->permisos()->attach($permisos);
    }
}
