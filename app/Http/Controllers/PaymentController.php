<?php

namespace App\Http\Controllers;

use App\Models\Trabajo;
use App\Models\Pago;
use App\Models\PagoPasarela;
use App\Models\CuotaPago;
use App\Services\PaymentService;
use App\Services\PagoFacilService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentController extends Controller
{
    protected $paymentService;
    protected $pagoFacilService;

    public function __construct(PaymentService $paymentService, PagoFacilService $pagoFacilService)
    {
        $this->paymentService = $paymentService;
        $this->pagoFacilService = $pagoFacilService;
    }

    /**
     * Muestra la página de selección de método de pago
     * IMPORTANTE: El cliente paga ANTES de iniciar el trabajo
     */
    public function selectMethod(Trabajo $trabajo)
    {
        // Verificar que haya un presupuesto aprobado
        $presupuestoAprobado = $trabajo->presupuestos()->where('estado', 'APROBADO')->exists();
        if (!$presupuestoAprobado) {
            return redirect()->route('trabajos.show', $trabajo->id_trabajo)
                ->withErrors(['error' => 'Debes aprobar el presupuesto primero.']);
        }

        // Verificar que no haya un pago completado
        $pagoCompletado = $trabajo->pagos()->where('estado', 'COMPLETADO')->exists();
        if ($pagoCompletado) {
            return redirect()->route('trabajos.show', $trabajo->id_trabajo)
                ->withErrors(['error' => 'Este trabajo ya ha sido pagado.']);
        }

        return Inertia::render('Client/Payment/SelectMethod', [
            'trabajo' => $trabajo->load('presupuestos'),
            'total' => (float) $trabajo->presupuestos()->where('estado', 'APROBADO')->sum('monto_total'),
        ]);
    }

    /**
     * Inicia el proceso de pago (crea el plan y genera QR si es necesario)
     */
    public function initiate(Request $request, Trabajo $trabajo)
    {
        $request->validate([
            'tipo_pago' => 'required|in:CONTADO,CREDITO',
            'numero_cuotas' => 'required_if:tipo_pago,CREDITO|integer|min:1|max:12',
            'metodo_pago' => 'required|string',
        ]);

        try {
            // Crear el plan de pagos
            $pago = $this->paymentService->createPaymentPlan(
                $trabajo,
                $request->tipo_pago,
                $request->numero_cuotas ?? 1,
                $request->metodo_pago
            );

            // Cargar relaciones necesarias
            $pago->load('trabajo');
            if ($request->tipo_pago === 'CREDITO') {
                $pago->load('cuotas');
            }

            // Generar QR con Pago Fácil
            if ($request->metodo_pago === 'QR_PAGOFACIL') {
                $user = auth()->user();
                
                // Determinar monto y descripción según tipo de pago
                if ($request->tipo_pago === 'CREDITO') {
                    // Para crédito, generar QR de la primera cuota
                    $primeraCuota = $pago->cuotas->first();
                    $monto = $primeraCuota->monto;
                    $descripcion = "Cuota 1/{$pago->numero_cuotas} - {$trabajo->titulo}";
                    $cuotaId = $primeraCuota->id_cuota;
                    
                    \Log::info("Initiate - Cuota ID asignado: " . $cuotaId);
                    \Log::info("Initiate - Primera cuota: " . json_encode($primeraCuota));
                } else {
                    // Para contado, generar QR del monto total
                    $monto = $pago->monto;
                    $descripcion = "Pago completo - {$trabajo->titulo}";
                    $cuotaId = null;
                }

                // Preparar datos para Pago Fácil
                $qrData = $this->pagoFacilService->prepareQRData([
                    'payment_method' => 4,
                    'client_name' => $user->nombre,
                    'document_type' => 1,
                    'document_id' => $user->ci ?? '0',
                    'phone_number' => $user->telefono ?? '00000000',
                    'email' => $user->email,
                    'payment_number' => 'PAY-' . $pago->id_pago . '-' . time(),
                    'amount' => $monto,
                    'currency' => 2,
                    'client_code' => 'CLI-' . $trabajo->id_cliente,
                    'callback_url' => config('services.pagofacil.ngrok_url') . '/api/pagofacil/callback',
                    'product_description' => $descripcion,
                ]);

                // Generar QR
                $response = $this->pagoFacilService->generateQR($qrData);

                if ($response['success']) {
                    $values = $response['data']['values'];
                    
                    \Log::info("Initiate - Antes de guardar pasarela, cuota_id = " . ($cuotaId ?? 'NULL'));
                    
                    // Guardar información de la pasarela
                    $pasarelaData = [
                        'id_pago' => $pago->id_pago,
                        'cuota_id' => $cuotaId,
                        'transaction_id' => $qrData['paymentNumber'],
                        'pf_transaction_id' => $values['transactionId'],
                        'payment_method_transaction_id' => $values['paymentMethodTransactionId'],
                        'referencia' => $values['transactionId'],
                        'estado' => 'PENDIENTE',
                        'pf_status' => $values['status'],
                        'expiration_date' => $values['expirationDate'],
                        'qr_base64' => $values['qrBase64'],
                        'checkout_url' => $values['checkoutUrl'] ?? null,
                        'deep_link' => $values['deepLink'] ?? null,
                        'qr_content_url' => $values['qrContentUrl'] ?? null,
                        'universal_url' => $values['universalUrl'] ?? null,
                        'respuesta_json' => json_encode($response['data']),
                        'fecha' => now(),
                    ];

                    PagoPasarela::create($pasarelaData);

                    // Recargar pasarela
                    $pago->load('pasarela');
                }
            }

            // Retornar respuesta JSON con el pago creado
            return response()->json([
                'success' => true,
                'pago' => $pago,
                'message' => $request->tipo_pago === 'CREDITO' 
                    ? 'Plan de cuotas creado exitosamente' 
                    : 'Pago iniciado exitosamente'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al iniciar pago', [
                'error' => $e->getMessage(),
                'trabajo_id' => $trabajo->id_trabajo,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al iniciar el pago: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Muestra la página de checkout con el QR generado
     */
    public function checkout(Pago $pago)
    {
        $user = auth()->user();
        
        // Verificar permisos: propietario o dueño del trabajo
        if (!$user->isPropietario() && $pago->trabajo->id_cliente !== $user->id_usuario) {
            abort(403, 'No autorizado');
        }
        
        // Cargar la información de la pasarela y relaciones necesarias
        $pago->load('pasarela', 'trabajo', 'cuotas');

        \Log::info('Checkout - Tipo de pago: ' . $pago->tipo_pago);
        \Log::info('Checkout - Pasarela cargada: ' . ($pago->pasarela ? 'SI' : 'NO'));

        // Preparar datos del QR
        $qrData = null;
        
        // Si es pago a crédito, buscar el QR de la primera cuota pendiente
        if ($pago->tipo_pago === 'CREDITO') {
            // Buscar la primera cuota pendiente
            $cuotaPendiente = $pago->cuotas()
                ->where('estado', 'PENDIENTE')
                ->orderBy('numero_cuota', 'asc')
                ->first();
            
            \Log::info('Checkout - Cuota pendiente encontrada: ' . ($cuotaPendiente ? 'SI' : 'NO'));
            
            if ($cuotaPendiente) {
                // Buscar el QR asociado a esta cuota (más reciente si hay varios)
                $pasarelaCuota = PagoPasarela::where('id_pago', $pago->id_pago)
                    ->where('cuota_id', $cuotaPendiente->id_cuota)
                    ->where('estado', 'PENDIENTE')
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                \Log::info('Checkout - Pasarela cuota encontrada: ' . ($pasarelaCuota ? 'SI' : 'NO'));
                \Log::info('Checkout - Buscando cuota_id: ' . $cuotaPendiente->id_cuota);
                
                if ($pasarelaCuota) {
                    $qrData = [
                        'qr_base64' => $pasarelaCuota->qr_base64,
                        'transaction_id' => $pasarelaCuota->pf_transaction_id,
                        'expiration_date' => $pasarelaCuota->expiration_date,
                        'checkout_url' => $pasarelaCuota->checkout_url,
                        'deep_link' => $pasarelaCuota->deep_link,
                        'qr_content_url' => $pasarelaCuota->qr_content_url,
                        'universal_url' => $pasarelaCuota->universal_url,
                        'cuota' => $cuotaPendiente,
                    ];
                }
            }
        } else {
            // Para pago de contado, usar la pasarela principal
            \Log::info('Checkout - Buscando pasarela de contado');
            if ($pago->pasarela) {
                \Log::info('Checkout - Pasarela encontrada, generando qrData');
                $qrData = [
                    'qr_base64' => $pago->pasarela->qr_base64,
                    'transaction_id' => $pago->pasarela->pf_transaction_id,
                    'expiration_date' => $pago->pasarela->expiration_date,
                    'checkout_url' => $pago->pasarela->checkout_url,
                    'deep_link' => $pago->pasarela->deep_link,
                    'qr_content_url' => $pago->pasarela->qr_content_url,
                    'universal_url' => $pago->pasarela->universal_url,
                ];
            }
        }

        \Log::info('Checkout - qrData final: ' . ($qrData ? 'TIENE DATOS' : 'NULL'));

        return Inertia::render('Client/Payment/Checkout', [
            'pago' => $pago,
            'qrData' => $qrData,
        ]);
    }

    /**
     * Genera un código QR con Pago Fácil
     */
    public function generateQR(Request $request, Trabajo $trabajo)
    {
        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'document_id' => 'required|string|max:50',
            'phone_number' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'amount' => 'required|numeric|min:0.01',
            'callback_url' => 'nullable|url',
            'product_description' => 'nullable|string|max:255',
        ]);

        $callbackUrl = $validated['callback_url'] ?? config('services.pagofacil.ngrok_url') . '/api/pagofacil/callback';

        try {
            $pago = Pago::create([
                'id_trabajo' => $trabajo->id_trabajo,
                'monto' => $validated['amount'],
                'tipo_pago' => 'CONTADO',
                'numero_cuotas' => 1,
                'monto_cuota' => $validated['amount'],
                'metodo' => 'PASARELA',
                'metodo_pago' => 'QR_PAGOFACIL',
                'estado' => 'PENDIENTE',
                'fecha' => now(),
            ]);

            $qrData = $this->pagoFacilService->prepareQRData([
                'payment_method' => 4,
                'client_name' => $validated['client_name'],
                'document_type' => 1,
                'document_id' => $validated['document_id'],
                'phone_number' => $validated['phone_number'],
                'email' => $validated['email'],
                'payment_number' => 'PAY-' . $pago->id_pago . '-' . time(),
                'amount' => $validated['amount'],
                'currency' => 2,
                'client_code' => 'CLI-' . $trabajo->id_cliente,
                'callback_url' => $callbackUrl,
                'product_description' => $validated['product_description'] ?? 'Pago de servicio',
            ]);

            $response = $this->pagoFacilService->generateQR($qrData);

            if (!$response['success']) {
                $pago->delete();
                return response()->json(['success' => false, 'error' => $response['error']], 400);
            }

            $values = $response['data']['values'];
            
            PagoPasarela::create([
                'id_pago' => $pago->id_pago,
                'transaction_id' => $qrData['paymentNumber'],
                'pf_transaction_id' => $values['transactionId'],
                'payment_method_transaction_id' => $values['paymentMethodTransactionId'],
                'referencia' => $values['transactionId'],
                'estado' => 'PENDIENTE',
                'pf_status' => $values['status'],
                'expiration_date' => $values['expirationDate'],
                'qr_base64' => $values['qrBase64'],
                'checkout_url' => $values['checkoutUrl'] ?? null,
                'deep_link' => $values['deepLink'] ?? null,
                'qr_content_url' => $values['qrContentUrl'] ?? null,
                'universal_url' => $values['universalUrl'] ?? null,
                'respuesta_json' => json_encode($response['data']),
                'fecha' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'QR generado exitosamente',
                'data' => [
                    'pago_id' => $pago->id_pago,
                    'transaction_id' => $values['transactionId'],
                    'qr_base64' => $values['qrBase64'],
                    'expiration_date' => $values['expirationDate'],
                    'checkout_url' => $values['checkoutUrl'] ?? null,
                    'deep_link' => $values['deepLink'] ?? null,
                    'qr_content_url' => $values['qrContentUrl'] ?? null,
                    'universal_url' => $values['universalUrl'] ?? null,
                ]
            ]);

        } catch (\Exception $e) {
            if (isset($pago)) {
                $pago->delete();
            }
            return response()->json(['success' => false, 'error' => 'Error al generar QR: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Maneja el callback de Pago Fácil
     */
    public function handleCallback(Request $request)
    {
        try {
            \Log::info('Pago Fácil Callback Received', ['data' => $request->all()]);

            $pedidoId = $request->input('PedidoID');
            $estado = $request->input('Estado');
            
            if (!$pedidoId) {
                return response()->json(['error' => 0, 'status' => 1, 'message' => 'Callback recibido', 'values' => true], 200);
            }

            $pagoPasarela = PagoPasarela::where('transaction_id', $pedidoId)
                ->orWhere('pf_transaction_id', $pedidoId)
                ->orWhere('payment_method_transaction_id', $pedidoId)
                ->first();

            if (!$pagoPasarela) {
                \Log::warning('Pago no encontrado', ['pedido_id' => $pedidoId]);
                return response()->json(['error' => 0, 'status' => 1, 'message' => 'Pago no encontrado', 'values' => true], 200);
            }

            $pago = $pagoPasarela->pago;

            if (strtolower($estado) === 'completado' || strtolower($estado) === 'pagado') {
                
                if ($pagoPasarela->cuota_id) {
                    $cuota = CuotaPago::find($pagoPasarela->cuota_id);
                    if ($cuota) {
                        $cuota->update(['estado' => 'PAGADA', 'fecha_pago' => now()]);
                        
                        $todasPagadas = $pago->cuotas()->where('estado', '!=', 'PAGADA')->count() === 0;
                        if ($todasPagadas) {
                            $pago->update(['estado' => 'PAGADO', 'fecha' => now()]);
                        }
                    }
                } else {
                    $pago->update(['estado' => 'PAGADO', 'fecha' => now()]);
                }

                $pagoPasarela->update(['estado' => 'COMPLETADO', 'pf_status' => 2, 'respuesta_json' => json_encode($request->all())]);
                
                $trabajo = $pago->trabajo;
                if ($trabajo && $trabajo->estado !== 'EN_PRODUCCION' && $trabajo->estado !== 'FINALIZADO') {
                    $inventoryService = new \App\Services\InventoryService();
                    $presupuestoAprobado = $trabajo->presupuestos()->where('estado', 'APROBADO')->first();
                    
                    if ($presupuestoAprobado) {
                        try {
                            $inventoryService->consumeMaterials($presupuestoAprobado->id_presupuesto);
                        } catch (\Exception $e) {
                            \Log::error('Error consumiendo materiales', ['error' => $e->getMessage()]);
                        }
                    }
                    
                    $trabajo->update(['estado' => 'EN_PRODUCCION', 'fecha_inicio' => now()]);
                }
            }

            return response()->json(['error' => 0, 'status' => 1, 'message' => 'Pago procesado correctamente', 'values' => true], 200);

        } catch (\Exception $e) {
            \Log::error('Error procesando callback', ['error' => $e->getMessage()]);
            return response()->json(['error' => 0, 'status' => 1, 'message' => 'Callback recibido', 'values' => true], 200);
        }
    }

    /**
     * Verifica el estado de un pago
     */
    public function checkPaymentStatus(Pago $pago)
    {
        $pago->load('pasarela');

        if ($pago->estado === 'PAGADO') {
            return response()->json([
                'success' => true,
                'data' => [
                    'pago_id' => $pago->id_pago,
                    'estado' => $pago->estado,
                    'monto' => $pago->monto,
                ]
            ]);
        }

        if ($pago->pasarela && $pago->pasarela->pf_transaction_id) {
            $queryResult = $this->pagoFacilService->queryTransaction(
                $pago->pasarela->pf_transaction_id,
                $pago->pasarela->transaction_id
            );

            if ($queryResult['success'] && isset($queryResult['data']['values'])) {
                $values = $queryResult['data']['values'];
                $paymentStatus = $values['paymentStatus'] ?? null;

                if ($paymentStatus == 2) {
                    $pago->update(['estado' => 'PAGADO', 'fecha' => now()]);
                    $pago->pasarela->update(['estado' => 'COMPLETADO', 'pf_status' => $paymentStatus]);
                    
                    $trabajo = $pago->trabajo;
                    if ($trabajo && $trabajo->estado !== 'EN_PRODUCCION' && $trabajo->estado !== 'FINALIZADO') {
                        $trabajo->update(['estado' => 'EN_PRODUCCION', 'fecha_inicio' => now()]);
                    }
                }
            }
        }

        $pago->refresh();
        return response()->json(['success' => true, 'data' => ['pago_id' => $pago->id_pago, 'estado' => $pago->estado]]);
    }

    /**
     * Genera QR para pagar una cuota individual
     */
    public function generateInstallmentQR(Request $request, $cuotaId)
    {
        try {
            $cuota = CuotaPago::findOrFail($cuotaId);
            
            if ($cuota->estado !== 'PENDIENTE') {
                return response()->json(['success' => false, 'message' => 'Esta cuota ya ha sido pagada.'], 400);
            }

            $pago = $cuota->pago;
            $trabajo = $pago->trabajo;
            $user = auth()->user();
            
            $qrData = $this->pagoFacilService->prepareQRData([
                'payment_method' => 4,
                'client_name' => $user->nombre,
                'document_type' => 1,
                'document_id' => $user->ci ?? '0',
                'phone_number' => $user->telefono ?? '00000000',
                'email' => $user->email,
                'payment_number' => 'CUOTA-' . $cuota->id_cuota . '-' . time(),
                'amount' => $cuota->monto,
                'currency' => 2,
                'client_code' => 'CLI-' . $trabajo->id_cliente,
                'callback_url' => config('services.pagofacil.ngrok_url') . '/api/pagofacil/callback',
                'product_description' => "Cuota {$cuota->numero_cuota}/{$pago->numero_cuotas} - {$trabajo->titulo}",
            ]);

            $response = $this->pagoFacilService->generateQR($qrData);

            if (!$response['success']) {
                throw new \Exception($response['error'] ?? 'Error al generar QR');
            }

            $values = $response['data']['values'];

            PagoPasarela::updateOrCreate(
                ['id_pago' => $pago->id_pago, 'cuota_id' => $cuota->id_cuota],
                [
                    'transaction_id' => $qrData['paymentNumber'],
                    'pf_transaction_id' => $values['transactionId'],
                    'payment_method_transaction_id' => $values['paymentMethodTransactionId'],
                    'referencia' => $values['transactionId'],
                    'qr_base64' => $values['qrBase64'],
                    'checkout_url' => $values['checkoutUrl'] ?? null,
                    'deep_link' => $values['deepLink'] ?? null,
                    'qr_content_url' => $values['qrContentUrl'] ?? null,
                    'universal_url' => $values['universalUrl'] ?? null,
                    'expiration_date' => $values['expirationDate'],
                    'estado' => 'PENDIENTE',
                    'pf_status' => $values['status'],
                    'respuesta_json' => json_encode($response['data']),
                    'fecha' => now(),
                ]
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'cuota' => $cuota,
                    'qr_base64' => $values['qrBase64'],
                    'transaction_id' => $values['transactionId'],
                    'expiration_date' => $values['expirationDate'],
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error generando QR para cuota', ['cuota_id' => $cuotaId, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error al generar QR: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Verifica el estado de pago de una cuota
     */
    public function checkInstallmentStatus($cuotaId)
    {
        try {
            $cuota = CuotaPago::findOrFail($cuotaId);
            return response()->json([
                'success' => true,
                'data' => [
                    'cuota_id' => $cuota->id_cuota,
                    'numero_cuota' => $cuota->numero_cuota,
                    'estado' => $cuota->estado,
                    'monto' => $cuota->monto,
                    'fecha_vencimiento' => $cuota->fecha_vencimiento,
                    'fecha_pago' => $cuota->fecha_pago,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al verificar estado de cuota'], 500);
        }
    }

    /**
     * Muestra las cuotas de un pago
     */
    public function showInstallments($pagoId)
    {
        $pago = Pago::with(['cuotas', 'trabajo.cliente'])->findOrFail($pagoId);
        $user = auth()->user();
        
        // Verificar permisos: propietario o dueño del trabajo
        if (!$user->isPropietario() && $pago->trabajo->id_cliente !== $user->id_usuario) {
            abort(403, 'No autorizado');
        }

        return Inertia::render('Client/Payment/Installments', [
            'pago' => $pago,
            'cuotas' => $pago->cuotas,
            'trabajo' => $pago->trabajo
        ]);
    }

    public function allInstallments()
    {
        $user = auth()->user();
        
        // Obtener todos los pagos del usuario (contado y crédito)
        $pagos = Pago::with(['cuotas', 'trabajo', 'pasarela'])
            ->whereHas('trabajo', function($q) use ($user) {
                $q->where('id_cliente', $user->id_usuario);
            })
            ->orderBy('fecha', 'desc')
            ->get();

        // Calcular estadísticas
        $stats = [
            'total_pagos' => $pagos->count(),
            'pagos_contado' => $pagos->where('tipo_pago', 'CONTADO')->count(),
            'pagos_credito' => $pagos->where('tipo_pago', 'CREDITO')->count(),
            'monto_total' => $pagos->sum('monto'),
            'monto_pendiente' => 0,
            'monto_pagado' => 0,
            'total_cuotas' => 0,
            'cuotas_pagadas' => 0,
            'cuotas_pendientes' => 0,
        ];

        // Calcular estadísticas de cuotas para pagos a crédito
        foreach ($pagos->where('tipo_pago', 'CREDITO') as $pago) {
            foreach ($pago->cuotas as $cuota) {
                $stats['total_cuotas']++;
                
                if ($cuota->estado === 'PAGADA') {
                    $stats['cuotas_pagadas']++;
                    $stats['monto_pagado'] += $cuota->monto;
                } else {
                    $stats['cuotas_pendientes']++;
                    $stats['monto_pendiente'] += $cuota->monto;
                }
            }
        }

        // Para pagos al contado, verificar estado de pasarela
        foreach ($pagos->where('tipo_pago', 'CONTADO') as $pago) {
            if ($pago->pasarela && $pago->pasarela->estado === 'COMPLETADO') {
                $stats['monto_pagado'] += $pago->monto;
            } else {
                $stats['monto_pendiente'] += $pago->monto;
            }
        }

        return Inertia::render('Client/Payment/PaymentHistory', [
            'pagos' => $pagos,
            'stats' => $stats
        ]);
    }

    // Admin methods
    public function adminIndex()
    {
        $pagos = Pago::with(['trabajo.cliente', 'cuotas', 'pasarela'])
            ->orderBy('fecha', 'desc')
            ->paginate(20);

        $stats = [
            'total_pagos' => Pago::count(),
            'pagos_contado' => Pago::where('tipo_pago', 'CONTADO')->count(),
            'pagos_credito' => Pago::where('tipo_pago', 'CREDITO')->count(),
            'monto_total' => Pago::sum('monto'),
            'monto_pendiente' => 0,
            'monto_pagado' => 0,
        ];

        // Calcular montos pagados y pendientes
        foreach (Pago::with(['cuotas', 'pasarela'])->get() as $pago) {
            if ($pago->tipo_pago === 'CREDITO') {
                foreach ($pago->cuotas as $cuota) {
                    if ($cuota->estado === 'PAGADA') {
                        $stats['monto_pagado'] += $cuota->monto;
                    } else {
                        $stats['monto_pendiente'] += $cuota->monto;
                    }
                }
            } else {
                if ($pago->pasarela && $pago->pasarela->estado === 'COMPLETADO') {
                    $stats['monto_pagado'] += $pago->monto;
                } else {
                    $stats['monto_pendiente'] += $pago->monto;
                }
            }
        }

        return Inertia::render('Admin/Payments/Index', [
            'pagos' => $pagos,
            'stats' => $stats
        ]);
    }

    public function clientPayments($clienteId)
    {
        $cliente = \App\Models\User::findOrFail($clienteId);
        
        $pagos = Pago::with(['trabajo', 'cuotas', 'pasarela'])
            ->whereHas('trabajo', function($q) use ($clienteId) {
                $q->where('id_cliente', $clienteId);
            })
            ->orderBy('fecha', 'desc')
            ->get();

        $stats = [
            'total_pagos' => $pagos->count(),
            'pagos_contado' => $pagos->where('tipo_pago', 'CONTADO')->count(),
            'pagos_credito' => $pagos->where('tipo_pago', 'CREDITO')->count(),
            'monto_total' => $pagos->sum('monto'),
            'monto_pendiente' => 0,
            'monto_pagado' => 0,
        ];

        foreach ($pagos as $pago) {
            if ($pago->tipo_pago === 'CREDITO') {
                foreach ($pago->cuotas as $cuota) {
                    if ($cuota->estado === 'PAGADA') {
                        $stats['monto_pagado'] += $cuota->monto;
                    } else {
                        $stats['monto_pendiente'] += $cuota->monto;
                    }
                }
            } else {
                if ($pago->pasarela && $pago->pasarela->estado === 'COMPLETADO') {
                    $stats['monto_pagado'] += $pago->monto;
                } else {
                    $stats['monto_pendiente'] += $pago->monto;
                }
            }
        }

        return Inertia::render('Admin/Payments/ClientPayments', [
            'cliente' => $cliente,
            'pagos' => $pagos,
            'stats' => $stats
        ]);
    }

    /**
     * Generar comprobante de pago en PDF
     */
    public function downloadComprobante(Pago $pago)
    {
        $user = auth()->user();
        
        // Verificar permisos
        if (!$user->isPropietario() && $pago->trabajo->id_cliente !== $user->id_usuario) {
            abort(403, 'No autorizado');
        }

        // Cargar relaciones
        $pago->load([
            'trabajo.cliente',
            'trabajo.servicio',
            'cuotas' => function($query) {
                $query->orderBy('numero_cuota');
            }
        ]);

        // Generar PDF
        $pdf = \PDF::loadView('pdf.comprobante-pago', [
            'pago' => $pago,
            'trabajo' => $pago->trabajo,
            'cliente' => $pago->trabajo->cliente,
        ]);

        $fileName = "Comprobante-Pago-{$pago->id_pago}-" . now()->format('Ymd') . ".pdf";
        
        return $pdf->download($fileName);
    }

    /**
     * Generar comprobante de cuota en PDF
     */
    public function downloadComprobanteCuota(CuotaPago $cuota)
    {
        $user = auth()->user();
        
        // Verificar permisos
        if (!$user->isPropietario() && $cuota->pago->trabajo->id_cliente !== $user->id_usuario) {
            abort(403, 'No autorizado');
        }

        // Verificar que la cuota esté pagada
        if ($cuota->estado !== 'PAGADA') {
            abort(400, 'La cuota no ha sido pagada');
        }

        // Cargar relaciones
        $cuota->load([
            'pago.trabajo.cliente',
            'pago.trabajo.servicio'
        ]);

        // Generar PDF
        $pdf = \PDF::loadView('pdf.comprobante-cuota', [
            'cuota' => $cuota,
            'pago' => $cuota->pago,
            'trabajo' => $cuota->pago->trabajo,
            'cliente' => $cuota->pago->trabajo->cliente,
        ]);

        $fileName = "Comprobante-Cuota-{$cuota->numero_cuota}-Pago-{$cuota->id_pago}-" . now()->format('Ymd') . ".pdf";
        
        return $pdf->download($fileName);
    }
}
