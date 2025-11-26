<?php

namespace Database\Seeders;

use App\Models\Trabajo;
use App\Models\User;
use Illuminate\Database\Seeder;

class TrabajoSeeder extends Seeder
{
    public function run(): void
    {
        $cliente1 = User::where('email', 'cliente1@test.com')->first();
        $cliente2 = User::where('email', 'cliente2@test.com')->first();

        if (!$cliente1 || !$cliente2) return;

        // Job 1: SOLICITADO
        Trabajo::create([
            'id_cliente' => $cliente1->id_usuario,
            'titulo' => 'Mesa de Comedor',
            'descripcion' => 'Mesa de roble para 8 personas, estilo rústico.',
            'estado' => 'SOLICITADO',
            'fecha_solicitud' => now()->subDays(2),
        ]);

        // Job 2: PRESUPUESTADO
        $trabajo2 = Trabajo::create([
            'id_cliente' => $cliente2->id_usuario,
            'titulo' => 'Estante de Libros',
            'descripcion' => 'Estante de melamina blanca de 2x2 metros.',
            'estado' => 'PRESUPUESTADO',
            'fecha_solicitud' => now()->subDays(5),
        ]);
        
        $trabajo2->presupuestos()->create([
            'total_materiales' => 500,
            'total_mano_obra' => 300,
            'total_otros' => 50,
            'total' => 850,
            'estado' => 'ENVIADO',
            'fecha_creacion' => now()->subDays(4),
        ]);

        // Job 3: EN_PRODUCCION
        $trabajo3 = Trabajo::create([
            'id_cliente' => $cliente1->id_usuario,
            'titulo' => 'Escritorio de Oficina',
            'descripcion' => 'Escritorio en L con cajonera.',
            'estado' => 'EN_PRODUCCION',
            'fecha_solicitud' => now()->subDays(10),
            'fecha_inicio' => now()->subDays(8),
        ]);

        $trabajo3->presupuestos()->create([
            'total_materiales' => 800,
            'total_mano_obra' => 400,
            'total_otros' => 100,
            'total' => 1300,
            'estado' => 'APROBADO',
            'fecha_creacion' => now()->subDays(9),
        ]);
    }
}
