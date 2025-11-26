<?php

namespace Database\Seeders;

use App\Models\Trabajo;
use App\Models\Pago;
use App\Models\CuotaPago;
use App\Models\User;
use App\Models\Servicio;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PagoSeeder extends Seeder
{
    public function run(): void
    {
        $propietario = User::where('rol', 'PROPIETARIO')->first();
        $clientes = User::where('rol', 'CLIENTE')->get();

        if ($clientes->count() < 2) {
            $this->command->error('Se requieren al menos 2 clientes para ejecutar este seeder');
            return;
        }

        $servicios = Servicio::take(3)->get();
        if ($servicios->count() < 3) {
            $this->command->error('Se requieren al menos 3 servicios para ejecutar este seeder');
            return;
        }

        // ============================================
        // TRABAJO 1: Banner Grande - PAGO AL CONTADO (COMPLETADO)
        // ============================================
        $trabajo1 = Trabajo::create([
            'id_cliente' => $clientes[0]->id_usuario,
            'id_servicio' => $servicios[0]->id_servicio,
            'titulo' => 'Banner promocional 3x2 metros',
            'descripcion' => 'Banner para promoción de apertura de local comercial',
            'estado' => 'FINALIZADO',
            'fecha_solicitud' => Carbon::now()->subDays(30),
            'fecha_inicio' => Carbon::now()->subDays(28),
            'fecha_fin' => Carbon::now()->subDays(25),
            'cantidad' => 1,
            'medidas' => json_encode(['ancho' => '3m', 'alto' => '2m']),
            'colores' => 'Rojo, Blanco, Negro',
        ]);

        // Presupuesto
        $presupuesto1 = $trabajo1->presupuestos()->create([
            'total_materiales' => 800.00,
            'total_mano_obra' => 500.00,
            'total_otros' => 200.00,
            'total' => 1500.00,
            'estado' => 'APROBADO',
            'fecha_creacion' => Carbon::now()->subDays(29),
            'fecha_respuesta' => Carbon::now()->subDays(28),
            'notas' => 'Incluye instalación',
        ]);

        // Pago al CONTADO - YA PAGADO
        $pago1 = Pago::create([
            'id_trabajo' => $trabajo1->id_trabajo,
            'monto' => 1500.00,
            'tipo_pago' => 'CONTADO',
            'metodo' => 'CONTADO', // ← CORRECTO: debe ser CONTADO, CREDITO o PASARELA
            'metodo_pago' => 'EFECTIVO',
            'estado' => 'PAGADO',
            'fecha' => Carbon::now()->subDays(25),
            'numero_cuotas' => null,
            'monto_cuota' => null,
        ]);

        // Seguimiento
        $trabajo1->seguimientos()->create([
            'descripcion' => 'Trabajo finalizado e instalado correctamente',
            'porcentaje_avance' => 100,
            'horas_trabajadas' => 8,
            'fecha' => Carbon::now()->subDays(25),
        ]);

        // ============================================
        // TRABAJO 2: Vinil Decorativo - PAGO A CRÉDITO 3 CUOTAS (TODAS PAGADAS)
        // ============================================
        $trabajo2 = Trabajo::create([
            'id_cliente' => $clientes[1]->id_usuario,
            'id_servicio' => $servicios[1]->id_servicio,
            'titulo' => 'Vinil decorativo para oficina',
            'descripcion' => 'Decoración de paredes con vinil adhesivo - logo empresarial',
            'estado' => 'FINALIZADO',
            'fecha_solicitud' => Carbon::now()->subDays(45),
            'fecha_inicio' => Carbon::now()->subDays(42),
            'fecha_fin' => Carbon::now()->subDays(38),
            'cantidad' => 15,
            'medidas' => json_encode(['superficie' => '15m²']),
            'colores' => 'Azul corporativo, Blanco',
        ]);

        $presupuesto2 = $trabajo2->presupuestos()->create([
            'total_materiales' => 1200.00,
            'total_mano_obra' => 800.00,
            'total_otros' => 100.00,
            'total' => 2100.00,
            'estado' => 'APROBADO',
            'fecha_creacion' => Carbon::now()->subDays(43),
            'fecha_respuesta' => Carbon::now()->subDays(42),
        ]);

        // Pago a CRÉDITO - 3 cuotas - TODAS PAGADAS
        $pago2 = Pago::create([
            'id_trabajo' => $trabajo2->id_trabajo,
            'monto' => 2100.00,
            'tipo_pago' => 'CREDITO',
            'metodo' => 'CREDITO',
            'metodo_pago' => 'CREDITO',
            'estado' => 'PAGADO',
            'fecha' => Carbon::now()->subDays(38),
            'numero_cuotas' => 3,
            'monto_cuota' => 700.00,
        ]);

        // Cuota 1 - PAGADA
        CuotaPago::create([
            'id_pago' => $pago2->id_pago,
            'numero_cuota' => 1,
            'monto' => 700.00,
            'fecha_vencimiento' => Carbon::now()->subDays(38),
            'fecha_pago' => Carbon::now()->subDays(38),
            'estado' => 'PAGADA',
        ]);

        // Cuota 2 - PAGADA
        CuotaPago::create([
            'id_pago' => $pago2->id_pago,
            'numero_cuota' => 2,
            'monto' => 700.00,
            'fecha_vencimiento' => Carbon::now()->subDays(8),
            'fecha_pago' => Carbon::now()->subDays(7),
            'estado' => 'PAGADA',
        ]);

        // Cuota 3 - PAGADA
        CuotaPago::create([
            'id_pago' => $pago2->id_pago,
            'numero_cuota' => 3,
            'monto' => 700.00,
            'fecha_vencimiento' => Carbon::now()->addDays(22),
            'fecha_pago' => Carbon::now()->subDays(1),
            'estado' => 'PAGADA',
        ]);

        // ============================================
        // TRABAJO 3: Letras Corporeas - PAGO A CRÉDITO 6 CUOTAS (3 PAGADAS, 3 PENDIENTES)
        // ============================================
        $trabajo3 = Trabajo::create([
            'id_cliente' => $clientes[0]->id_usuario,
            'id_servicio' => $servicios[2]->id_servicio,
            'titulo' => 'Letras corpóreas iluminadas',
            'descripcion' => 'Letras 3D con iluminación LED para fachada de tienda',
            'estado' => 'FINALIZADO',
            'fecha_solicitud' => Carbon::now()->subDays(60),
            'fecha_inicio' => Carbon::now()->subDays(55),
            'fecha_fin' => Carbon::now()->subDays(45),
            'cantidad' => 10,
            'medidas' => json_encode(['altura' => '50cm', 'profundidad' => '5cm']),
            'colores' => 'Dorado con LED blanco',
        ]);

        $presupuesto3 = $trabajo3->presupuestos()->create([
            'total_materiales' => 2500.00,
            'total_mano_obra' => 1800.00,
            'total_otros' => 700.00,
            'total' => 5000.00,
            'estado' => 'APROBADO',
            'fecha_creacion' => Carbon::now()->subDays(57),
            'fecha_respuesta' => Carbon::now()->subDays(55),
            'notas' => 'Incluye instalación eléctrica',
        ]);

        // Pago a CRÉDITO - 6 cuotas - PARCIALMENTE PAGADO
        $pago3 = Pago::create([
            'id_trabajo' => $trabajo3->id_trabajo,
            'monto' => 5000.00,
            'tipo_pago' => 'CREDITO',
            'metodo' => 'CREDITO',
            'metodo_pago' => 'CREDITO',
            'estado' => 'EN_REVISION',
            'fecha' => Carbon::now()->subDays(45),
            'numero_cuotas' => 6,
            'monto_cuota' => 833.33,
        ]);

        // Cuota 1 - PAGADA
        CuotaPago::create([
            'id_pago' => $pago3->id_pago,
            'numero_cuota' => 1,
            'monto' => 833.33,
            'fecha_vencimiento' => Carbon::now()->subDays(45),
            'fecha_pago' => Carbon::now()->subDays(45),
            'estado' => 'PAGADA',
        ]);

        // Cuota 2 - PAGADA
        CuotaPago::create([
            'id_pago' => $pago3->id_pago,
            'numero_cuota' => 2,
            'monto' => 833.33,
            'fecha_vencimiento' => Carbon::now()->subDays(15),
            'fecha_pago' => Carbon::now()->subDays(14),
            'estado' => 'PAGADA',
        ]);

        // Cuota 3 - PAGADA
        CuotaPago::create([
            'id_pago' => $pago3->id_pago,
            'numero_cuota' => 3,
            'monto' => 833.33,
            'fecha_vencimiento' => Carbon::now()->addDays(15),
            'fecha_pago' => Carbon::now()->subDays(2),
            'estado' => 'PAGADA',
        ]);

        // Cuota 4 - PENDIENTE
        CuotaPago::create([
            'id_pago' => $pago3->id_pago,
            'numero_cuota' => 4,
            'monto' => 833.33,
            'fecha_vencimiento' => Carbon::now()->addDays(45),
            'estado' => 'PENDIENTE',
        ]);

        // Cuota 5 - PENDIENTE
        CuotaPago::create([
            'id_pago' => $pago3->id_pago,
            'numero_cuota' => 5,
            'monto' => 833.33,
            'fecha_vencimiento' => Carbon::now()->addDays(75),
            'estado' => 'PENDIENTE',
        ]);

        // Cuota 6 - PENDIENTE
        CuotaPago::create([
            'id_pago' => $pago3->id_pago,
            'numero_cuota' => 6,
            'monto' => 833.35,
            'fecha_vencimiento' => Carbon::now()->addDays(105),
            'estado' => 'PENDIENTE',
        ]);

        // ============================================
        // TRABAJO 4: Rotulación Vehicular - PAGO AL CONTADO (COMPLETADO)
        // ============================================
        $trabajo4 = Trabajo::create([
            'id_cliente' => $clientes[1]->id_usuario,
            'id_servicio' => $servicios[0]->id_servicio,
            'titulo' => 'Rotulación de flota vehicular',
            'descripcion' => 'Rotulación completa de 3 vehículos de reparto',
            'estado' => 'FINALIZADO',
            'fecha_solicitud' => Carbon::now()->subDays(20),
            'fecha_inicio' => Carbon::now()->subDays(18),
            'fecha_fin' => Carbon::now()->subDays(15),
            'cantidad' => 3,
            'colores' => 'Verde corporativo, Blanco, Negro',
        ]);

        $presupuesto4 = $trabajo4->presupuestos()->create([
            'total_materiales' => 1800.00,
            'total_mano_obra' => 1200.00,
            'total_otros' => 300.00,
            'total' => 3300.00,
            'estado' => 'APROBADO',
            'fecha_creacion' => Carbon::now()->subDays(19),
            'fecha_respuesta' => Carbon::now()->subDays(18),
        ]);

        // Pago al CONTADO - YA PAGADO
        Pago::create([
            'id_trabajo' => $trabajo4->id_trabajo,
            'monto' => 3300.00,
            'tipo_pago' => 'CONTADO',
            'metodo' => 'CONTADO', // ← CORRECTO
            'metodo_pago' => 'TRANSFERENCIA',
            'estado' => 'PAGADO',
            'fecha' => Carbon::now()->subDays(15),
            'transaction_id' => 'TRX-' . strtoupper(uniqid()),
        ]);

        // ============================================
        // TRABAJO 5: Señalética - PAGO A CRÉDITO 12 CUOTAS (1 PAGADA, 11 PENDIENTES)
        // ============================================
        $trabajo5 = Trabajo::create([
            'id_cliente' => $clientes[0]->id_usuario,
            'id_servicio' => $servicios[1]->id_servicio,
            'titulo' => 'Sistema de señalética corporativa',
            'descripcion' => 'Señalización completa para edificio de oficinas (piso 1-5)',
            'estado' => 'FINALIZADO',
            'fecha_solicitud' => Carbon::now()->subDays(10),
            'fecha_inicio' => Carbon::now()->subDays(8),
            'fecha_fin' => Carbon::now()->subDays(3),
            'cantidad' => 45,
            'medidas' => json_encode(['varios_tamaños' => true]),
        ]);

        $presupuesto5 = $trabajo5->presupuestos()->create([
            'total_materiales' => 3500.00,
            'total_mano_obra' => 2500.00,
            'total_otros' => 1000.00,
            'total' => 7000.00,
            'estado' => 'APROBADO',
            'fecha_creacion' => Carbon::now()->subDays(9),
            'fecha_respuesta' => Carbon::now()->subDays(8),
        ]);

        // Pago a CRÉDITO - 12 cuotas
        $pago5 = Pago::create([
            'id_trabajo' => $trabajo5->id_trabajo,
            'monto' => 7000.00,
            'tipo_pago' => 'CREDITO',
            'metodo' => 'CREDITO',
            'metodo_pago' => 'CREDITO',
            'estado' => 'EN_REVISION',
            'fecha' => Carbon::now()->subDays(3),
            'numero_cuotas' => 12,
            'monto_cuota' => 583.33,
        ]);

        // Cuota 1 - PAGADA (pago inicial)
        CuotaPago::create([
            'id_pago' => $pago5->id_pago,
            'numero_cuota' => 1,
            'monto' => 583.33,
            'fecha_vencimiento' => Carbon::now()->subDays(3),
            'fecha_pago' => Carbon::now()->subDays(3),
            'estado' => 'PAGADA',
        ]);

        // Generar las 11 cuotas restantes PENDIENTES
        for ($i = 2; $i <= 12; $i++) {
            $monto = ($i === 12) ? 583.37 : 583.33; // Ajuste en última cuota
            CuotaPago::create([
                'id_pago' => $pago5->id_pago,
                'numero_cuota' => $i,
                'monto' => $monto,
                'fecha_vencimiento' => Carbon::now()->addDays(30 * ($i - 1)),
                'estado' => 'PENDIENTE',
            ]);
        }

        $this->command->info('✅ Seeder de pagos completado:');
        $this->command->info('   - 2 trabajos con pago AL CONTADO (PAGADOS)');
        $this->command->info('   - 1 trabajo con CRÉDITO 3 cuotas (TODAS PAGADAS)');
        $this->command->info('   - 1 trabajo con CRÉDITO 6 cuotas (3 PAGADAS, 3 PENDIENTES)');
        $this->command->info('   - 1 trabajo con CRÉDITO 12 cuotas (1 PAGADA, 11 PENDIENTES)');
    }
}
