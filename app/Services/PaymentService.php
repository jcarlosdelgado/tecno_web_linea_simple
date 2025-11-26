<?php

namespace App\Services;

use App\Models\Pago;
use App\Models\CuotaPago;
use App\Models\Trabajo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentService
{
    /**
     * Crea un plan de pagos (Contado o Crédito)
     */
    public function createPaymentPlan(Trabajo $trabajo, string $tipoPago, int $numeroCuotas = 1, string $metodoPago = 'EFECTIVO')
    {
        return DB::transaction(function () use ($trabajo, $tipoPago, $numeroCuotas, $metodoPago) {
            // Calcular monto total (podría incluir intereses si fuera necesario)
            $montoTotal = $trabajo->presupuestos()->where('estado', 'APROBADO')->sum('monto_total');

            // Crear registro de pago principal
            $pago = Pago::create([
                'id_trabajo' => $trabajo->id_trabajo,
                'monto' => $montoTotal,
                'tipo_pago' => $tipoPago,
                'numero_cuotas' => $numeroCuotas,
                'monto_cuota' => $tipoPago === 'CREDITO' ? $montoTotal / $numeroCuotas : $montoTotal,
                'metodo' => $tipoPago === 'CONTADO' ? 'CONTADO' : 'CREDITO', // Legacy enum support
                'metodo_pago' => $metodoPago,
                'estado' => 'PENDIENTE',
                'fecha' => now(),
            ]);

            // Si es crédito, generar cuotas
            if ($tipoPago === 'CREDITO') {
                $this->generateInstallments($pago, $montoTotal, $numeroCuotas);
            }

            return $pago;
        });
    }

    /**
     * Genera las cuotas para un pago a crédito
     */
    private function generateInstallments(Pago $pago, float $total, int $cuotas)
    {
        $montoCuota = $total / $cuotas;
        $fechaVencimiento = now();

        for ($i = 1; $i <= $cuotas; $i++) {
            // Primera cuota vence hoy (o a definir), siguientes cada mes
            if ($i > 1) {
                $fechaVencimiento->addMonth();
            }

            CuotaPago::create([
                'id_pago' => $pago->id_pago,
                'numero_cuota' => $i,
                'monto' => $montoCuota,
                'fecha_vencimiento' => $fechaVencimiento->copy(),
                'estado' => 'PENDIENTE',
            ]);
        }
    }

    /**
     * Simula una transacción con la API de Pago Fácil
     */
    public function simulatePagoFacilTransaction(Pago $pago)
    {
        // En una implementación real, aquí se llamaría a la API de Pago Fácil
        // para generar el QR.
        
        // Simulamos un ID de transacción y una URL de QR ficticia
        $transactionId = 'PF-' . Str::random(10);
        $qrImage = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=PagoFacil-Simulado-' . $pago->id_pago;

        $pago->update([
            'transaction_id' => $transactionId,
            'metodo_pago' => 'QR_PAGOFACIL',
        ]);

        return [
            'success' => true,
            'transaction_id' => $transactionId,
            'qr_image' => $qrImage,
            'message' => 'QR Generado exitosamente (Simulación)',
        ];
    }

    /**
     * Confirma un pago (Callback simulado)
     */
    public function confirmPayment(Pago $pago)
    {
        $pago->update([
            'estado' => 'PAGADO',
            'fecha' => now(),
        ]);

        // Si es cuota única (contado), marcar como finalizado
        if ($pago->tipo_pago === 'CONTADO') {
            // Lógica adicional si es necesario
        }
        
        return $pago;
    }
}
