<?php

namespace Database\Seeders;

use App\Models\Proveedor;
use Illuminate\Database\Seeder;

class ProveedorSeeder extends Seeder
{
    public function run(): void
    {
        Proveedor::create([
            'nombre' => 'Ferretería El Clavo',
            'contacto' => 'Pedro Perez',
            'telefono' => '3333333',
            'email' => 'ventas@elclavo.com',
            'direccion' => 'Av. Brasil',
        ]);

        Proveedor::create([
            'nombre' => 'Maderas del Oriente',
            'contacto' => 'Ana Lopez',
            'telefono' => '4444444',
            'email' => 'info@maderasoriente.com',
            'direccion' => 'Parque Industrial',
        ]);
    }
}
