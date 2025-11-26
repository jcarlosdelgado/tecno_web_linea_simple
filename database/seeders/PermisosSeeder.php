<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permiso;

class PermisosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permisos = [
            // Módulo: Trabajos
            ['nombre' => 'ver_trabajos', 'descripcion' => 'Ver lista de trabajos', 'modulo' => 'trabajos'],
            ['nombre' => 'crear_trabajos', 'descripcion' => 'Crear nuevos trabajos', 'modulo' => 'trabajos'],
            ['nombre' => 'editar_trabajos', 'descripcion' => 'Editar trabajos existentes', 'modulo' => 'trabajos'],
            ['nombre' => 'eliminar_trabajos', 'descripcion' => 'Eliminar trabajos', 'modulo' => 'trabajos'],
            ['nombre' => 'cambiar_estado_trabajos', 'descripcion' => 'Cambiar estado de trabajos', 'modulo' => 'trabajos'],
            
            // Módulo: Presupuestos
            ['nombre' => 'ver_presupuestos', 'descripcion' => 'Ver presupuestos', 'modulo' => 'presupuestos'],
            ['nombre' => 'crear_presupuestos', 'descripcion' => 'Crear presupuestos', 'modulo' => 'presupuestos'],
            ['nombre' => 'editar_presupuestos', 'descripcion' => 'Editar presupuestos', 'modulo' => 'presupuestos'],
            ['nombre' => 'aprobar_presupuestos', 'descripcion' => 'Aprobar o rechazar presupuestos', 'modulo' => 'presupuestos'],
            
            // Módulo: Pagos
            ['nombre' => 'ver_pagos', 'descripcion' => 'Ver historial de pagos', 'modulo' => 'pagos'],
            ['nombre' => 'registrar_pagos', 'descripcion' => 'Registrar nuevos pagos', 'modulo' => 'pagos'],
            ['nombre' => 'editar_pagos', 'descripcion' => 'Editar información de pagos', 'modulo' => 'pagos'],
            ['nombre' => 'eliminar_pagos', 'descripcion' => 'Eliminar pagos', 'modulo' => 'pagos'],
            ['nombre' => 'ver_comprobantes', 'descripcion' => 'Descargar comprobantes de pago', 'modulo' => 'pagos'],
            
            // Módulo: Inventario/Materiales
            ['nombre' => 'ver_inventario', 'descripcion' => 'Ver inventario de materiales', 'modulo' => 'inventario'],
            ['nombre' => 'crear_materiales', 'descripcion' => 'Agregar nuevos materiales', 'modulo' => 'inventario'],
            ['nombre' => 'editar_materiales', 'descripcion' => 'Editar materiales', 'modulo' => 'inventario'],
            ['nombre' => 'eliminar_materiales', 'descripcion' => 'Eliminar materiales', 'modulo' => 'inventario'],
            ['nombre' => 'ver_movimientos', 'descripcion' => 'Ver movimientos de inventario', 'modulo' => 'inventario'],
            ['nombre' => 'registrar_movimientos', 'descripcion' => 'Registrar entradas/salidas', 'modulo' => 'inventario'],
            
            // Módulo: Proveedores
            ['nombre' => 'ver_proveedores', 'descripcion' => 'Ver lista de proveedores', 'modulo' => 'proveedores'],
            ['nombre' => 'crear_proveedores', 'descripcion' => 'Agregar nuevos proveedores', 'modulo' => 'proveedores'],
            ['nombre' => 'editar_proveedores', 'descripcion' => 'Editar proveedores', 'modulo' => 'proveedores'],
            ['nombre' => 'eliminar_proveedores', 'descripcion' => 'Eliminar proveedores', 'modulo' => 'proveedores'],
            
            // Módulo: Usuarios
            ['nombre' => 'ver_usuarios', 'descripcion' => 'Ver lista de usuarios', 'modulo' => 'usuarios'],
            ['nombre' => 'crear_usuarios', 'descripcion' => 'Crear nuevos usuarios', 'modulo' => 'usuarios'],
            ['nombre' => 'editar_usuarios', 'descripcion' => 'Editar información de usuarios', 'modulo' => 'usuarios'],
            ['nombre' => 'eliminar_usuarios', 'descripcion' => 'Eliminar usuarios', 'modulo' => 'usuarios'],
            ['nombre' => 'asignar_roles', 'descripcion' => 'Asignar roles a usuarios', 'modulo' => 'usuarios'],
            
            // Módulo: Seguimiento
            ['nombre' => 'ver_seguimiento', 'descripcion' => 'Ver seguimiento de trabajos', 'modulo' => 'seguimiento'],
            ['nombre' => 'crear_seguimiento', 'descripcion' => 'Registrar avances de trabajo', 'modulo' => 'seguimiento'],
            ['nombre' => 'editar_seguimiento', 'descripcion' => 'Editar seguimientos', 'modulo' => 'seguimiento'],
            ['nombre' => 'eliminar_seguimiento', 'descripcion' => 'Eliminar seguimientos', 'modulo' => 'seguimiento'],
            
            // Módulo: Reportes
            ['nombre' => 'ver_reportes', 'descripcion' => 'Ver reportes y estadísticas', 'modulo' => 'reportes'],
            ['nombre' => 'exportar_reportes', 'descripcion' => 'Exportar reportes PDF/Excel', 'modulo' => 'reportes'],
            
            // Módulo: Servicios
            ['nombre' => 'ver_servicios', 'descripcion' => 'Ver catálogo de servicios', 'modulo' => 'servicios'],
            ['nombre' => 'crear_servicios', 'descripcion' => 'Crear nuevos servicios', 'modulo' => 'servicios'],
            ['nombre' => 'editar_servicios', 'descripcion' => 'Editar servicios', 'modulo' => 'servicios'],
            ['nombre' => 'eliminar_servicios', 'descripcion' => 'Eliminar servicios', 'modulo' => 'servicios'],
            
            // Módulo: Gastos
            ['nombre' => 'ver_gastos', 'descripcion' => 'Ver gastos operativos', 'modulo' => 'gastos'],
            ['nombre' => 'registrar_gastos', 'descripcion' => 'Registrar gastos', 'modulo' => 'gastos'],
            ['nombre' => 'editar_gastos', 'descripcion' => 'Editar gastos', 'modulo' => 'gastos'],
            ['nombre' => 'eliminar_gastos', 'descripcion' => 'Eliminar gastos', 'modulo' => 'gastos'],
        ];

        foreach ($permisos as $permiso) {
            Permiso::create($permiso);
        }
    }
}
