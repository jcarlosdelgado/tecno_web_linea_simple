<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Servicio;

class ServicioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $servicios = [
            [
                'nombre' => 'Diseño de Banners Publicitarios',
                'descripcion' => 'Diseño e impresión de banners publicitarios de alta calidad para eventos, promociones y publicidad exterior. Incluye diseño personalizado y materiales resistentes.',
                'precio_base' => 150.00,
                'categoria' => 'Publicidad',
                'activo' => true,
                'orden' => 1,
            ],
            [
                'nombre' => 'Vinilos Decorativos',
                'descripcion' => 'Vinilos decorativos personalizados para paredes, ventanas y superficies. Diseños únicos que transforman espacios comerciales y residenciales.',
                'precio_base' => 80.00,
                'categoria' => 'Decoración',
                'activo' => true,
                'orden' => 2,
            ],
            [
                'nombre' => 'Diseño de Interiores Comerciales',
                'descripcion' => 'Diseño integral de interiores para locales comerciales, oficinas y espacios de trabajo. Incluye planos, renders 3D y supervisión de obra.',
                'precio_base' => 500.00,
                'categoria' => 'Diseño de Interiores',
                'activo' => true,
                'orden' => 3,
            ],
            [
                'nombre' => 'Señalética Corporativa',
                'descripcion' => 'Diseño y fabricación de señalética corporativa para empresas. Incluye letreros, directorios, señales de seguridad y wayfinding.',
                'precio_base' => 200.00,
                'categoria' => 'Señalética',
                'activo' => true,
                'orden' => 4,
            ],
            [
                'nombre' => 'Rotulación de Vehículos',
                'descripcion' => 'Rotulación profesional de vehículos con vinilos de alta durabilidad. Convierte tu vehículo en publicidad móvil.',
                'precio_base' => 300.00,
                'categoria' => 'Publicidad',
                'activo' => true,
                'orden' => 5,
            ],
            [
                'nombre' => 'Stands para Ferias',
                'descripcion' => 'Diseño y montaje de stands para ferias y eventos. Estructuras modulares, atractivas y funcionales.',
                'precio_base' => 800.00,
                'categoria' => 'Eventos',
                'activo' => true,
                'orden' => 6,
            ],
        ];

        foreach ($servicios as $servicio) {
            Servicio::create($servicio);
        }
    }
}
