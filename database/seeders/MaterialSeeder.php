<?php

namespace Database\Seeders;

use App\Models\Material;
use Illuminate\Database\Seeder;

class MaterialSeeder extends Seeder
{
    public function run(): void
    {
        $materiales = [
            [
                'nombre' => 'Vinilo Adhesivo Blanco',
                'descripcion' => 'Vinilo adhesivo de alta calidad para rotulación',
                'unidad_medida' => 'metros',
                'cantidad_disponible' => 100,
                'precio_unitario' => 0.01,
            ],
            [
                'nombre' => 'Vinilo Adhesivo Negro',
                'descripcion' => 'Vinilo adhesivo negro mate para rotulación',
                'unidad_medida' => 'metros',
                'cantidad_disponible' => 80,
                'precio_unitario' => 0.01,
            ],
            [
                'nombre' => 'Lona Banner 440g',
                'descripcion' => 'Lona para impresión de banners publicitarios',
                'unidad_medida' => 'metros',
                'cantidad_disponible' => 50,
                'precio_unitario' => 0.01,
            ],
            [
                'nombre' => 'Tinta Ecosolvente Cyan',
                'descripcion' => 'Tinta para impresora de gran formato',
                'unidad_medida' => 'litros',
                'cantidad_disponible' => 15,
                'precio_unitario' => 0.01,
            ],
            [
                'nombre' => 'Tinta Ecosolvente Magenta',
                'descripcion' => 'Tinta para impresora de gran formato',
                'unidad_medida' => 'litros',
                'cantidad_disponible' => 15,
                'precio_unitario' => 0.01,
            ],
            [
                'nombre' => 'Tinta Ecosolvente Amarillo',
                'descripcion' => 'Tinta para impresora de gran formato',
                'unidad_medida' => 'litros',
                'cantidad_disponible' => 15,
                'precio_unitario' => 0.01,
            ],
            [
                'nombre' => 'Tinta Ecosolvente Negro',
                'descripcion' => 'Tinta para impresora de gran formato',
                'unidad_medida' => 'litros',
                'cantidad_disponible' => 20,
                'precio_unitario' => 0.01,
            ],
            [
                'nombre' => 'Estructura Metálica 2x1m',
                'descripcion' => 'Estructura para stands y exhibidores',
                'unidad_medida' => 'unidades',
                'cantidad_disponible' => 25,
                'precio_unitario' => 0.03,
            ],
            [
                'nombre' => 'Acrílico Transparente 3mm',
                'descripcion' => 'Plancha de acrílico para señalética',
                'unidad_medida' => 'planchas',
                'cantidad_disponible' => 30,
                'precio_unitario' => 0.03,
            ],
            [
                'nombre' => 'Letras Corpóreas PVC',
                'descripcion' => 'Letras en PVC para rotulación',
                'unidad_medida' => 'unidades',
                'cantidad_disponible' => 100,
                'precio_unitario' => 0.01,
            ],
            [
                'nombre' => 'Pegamento Industrial',
                'descripcion' => 'Adhesivo de contacto para instalaciones',
                'unidad_medida' => 'galones',
                'cantidad_disponible' => 12,
                'precio_unitario' => 0.03,
            ],
            [
                'nombre' => 'Cinta de Montaje 3M',
                'descripcion' => 'Cinta adhesiva de doble cara',
                'unidad_medida' => 'rollos',
                'cantidad_disponible' => 40,
                'precio_unitario' => 0.03,
            ],
        ];

        $proveedores = \App\Models\Proveedor::all();

        foreach ($materiales as $data) {
            $material = Material::create($data);
            
            // Assign 1 or 2 random providers to each material
            if ($proveedores->count() > 0) {
                $randomProveedores = $proveedores->random(rand(1, min(2, $proveedores->count())));
                
                foreach ($randomProveedores as $index => $proveedor) {
                    $material->proveedores()->attach($proveedor->id_proveedor, [
                        'precio_unitario' => $data['precio_unitario'] * rand(90, 110) / 100, // Variation of price
                        'es_principal' => $index === 0, // First one is principal
                        'creado_en' => now(),
                        'actualizado_en' => now(),
                    ]);
                }
            }
        }
    }
}
