<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permiso;

class RolesEjemploSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Diseñador - Trabaja en diseños y presupuestos
        $disenador = Role::create([
            'nombre' => 'Diseñador',
            'descripcion' => 'Crea diseños, trabaja en presupuestos y ve trabajos asignados',
            'activo' => true,
        ]);
        
        $permisosDisenador = Permiso::whereIn('nombre', [
            'ver_trabajos',
            'editar_trabajos',
            'ver_presupuestos',
            'crear_presupuestos',
            'editar_presupuestos',
            'ver_servicios',
            'ver_materiales',
        ])->pluck('id_permiso');
        
        $disenador->permisos()->attach($permisosDisenador);

        // 2. Instalador - Ejecuta trabajos y registra avances
        $instalador = Role::create([
            'nombre' => 'Instalador',
            'descripcion' => 'Instala trabajos, registra seguimiento y usa materiales del inventario',
            'activo' => true,
        ]);
        
        $permisosInstalador = Permiso::whereIn('nombre', [
            'ver_trabajos',
            'ver_presupuestos',
            'ver_seguimiento',
            'crear_seguimiento',
            'editar_seguimiento',
            'ver_inventario',
            'registrar_movimientos',
            'ver_materiales',
        ])->pluck('id_permiso');
        
        $instalador->permisos()->attach($permisosInstalador);

        // 3. Contador - Gestiona finanzas
        $contador = Role::create([
            'nombre' => 'Contador',
            'descripcion' => 'Gestiona pagos, gastos y genera reportes financieros',
            'activo' => true,
        ]);
        
        $permisosContador = Permiso::whereIn('nombre', [
            'ver_pagos',
            'editar_pagos',
            'ver_comprobantes',
            'ver_gastos',
            'registrar_gastos',
            'editar_gastos',
            'eliminar_gastos',
            'ver_reportes',
            'exportar_reportes',
            'ver_trabajos',
        ])->pluck('id_permiso');
        
        $contador->permisos()->attach($permisosContador);

        // 4. Vendedor - Atiende clientes y gestiona trabajos
        $vendedor = Role::create([
            'nombre' => 'Vendedor',
            'descripcion' => 'Atiende clientes, crea trabajos y presupuestos',
            'activo' => true,
        ]);
        
        $permisosVendedor = Permiso::whereIn('nombre', [
            'ver_trabajos',
            'crear_trabajos',
            'editar_trabajos',
            'ver_presupuestos',
            'crear_presupuestos',
            'ver_usuarios',
            'ver_servicios',
            'ver_pagos',
        ])->pluck('id_permiso');
        
        $vendedor->permisos()->attach($permisosVendedor);

        // 5. Almacenero - Gestiona inventario y proveedores
        $almacenero = Role::create([
            'nombre' => 'Almacenero',
            'descripcion' => 'Gestiona inventario, materiales y proveedores',
            'activo' => true,
        ]);
        
        $permisosAlmacenero = Permiso::whereIn('nombre', [
            'ver_inventario',
            'crear_materiales',
            'editar_materiales',
            'eliminar_materiales',
            'ver_movimientos',
            'registrar_movimientos',
            'ver_proveedores',
            'crear_proveedores',
            'editar_proveedores',
            'ver_trabajos',
        ])->pluck('id_permiso');
        
        $almacenero->permisos()->attach($permisosAlmacenero);

        // 6. Empleado General - Permisos básicos para cualquier empleado
        $empleado = Role::create([
            'nombre' => 'Empleado',
            'descripcion' => 'Rol básico para empleados con permisos de visualización general',
            'activo' => true,
        ]);
        
        $permisosEmpleado = Permiso::whereIn('nombre', [
            'ver_trabajos',
            'ver_presupuestos',
            'ver_servicios',
            'ver_materiales',
            'ver_inventario',
            'ver_seguimiento',
            'ver_pagos',
        ])->pluck('id_permiso');
        
        $empleado->permisos()->attach($permisosEmpleado);
    }
}
