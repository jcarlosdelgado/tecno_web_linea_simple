<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Pago #{{ $pago->id_pago }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #10b981;
        }
        
        .header h1 {
            color: #10b981;
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .header .subtitle {
            color: #666;
            font-size: 16px;
            font-weight: bold;
        }
        
        .header p {
            color: #666;
            font-size: 12px;
            margin-top: 5px;
        }
        
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .info-column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 10px;
        }
        
        .info-box {
            background-color: #f0fdf4;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #10b981;
        }
        
        .info-box h3 {
            color: #10b981;
            font-size: 14px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        
        .info-box p {
            margin: 5px 0;
            font-size: 12px;
        }
        
        .info-box strong {
            color: #047857;
        }
        
        .payment-details {
            background-color: #ecfdf5;
            padding: 20px;
            border-radius: 8px;
            border: 2px solid #10b981;
            margin: 25px 0;
        }
        
        .payment-details h3 {
            color: #10b981;
            font-size: 16px;
            margin-bottom: 15px;
            text-transform: uppercase;
            text-align: center;
        }
        
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            color: rgba(16, 185, 129, 0.05);
            font-weight: bold;
            z-index: -1;
        }
        
        .payment-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #d1fae5;
        }
        
        .payment-row:last-child {
            border-bottom: none;
        }
        
        .payment-row.total {
            border-top: 2px solid #10b981;
            margin-top: 10px;
            padding-top: 15px;
            font-size: 18px;
            font-weight: bold;
            color: #10b981;
        }
        
        .payment-row .label {
            color: #047857;
            font-weight: 600;
        }
        
        .payment-row .value {
            color: #333;
            font-weight: 600;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        thead {
            background-color: #10b981;
            color: white;
        }
        
        th {
            padding: 12px 8px;
            text-align: left;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
        }
        
        td {
            padding: 10px 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .transaction-info {
            background-color: #fef3c7;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #f59e0b;
            margin: 20px 0;
        }
        
        .transaction-info h4 {
            color: #d97706;
            margin-bottom: 10px;
            font-size: 13px;
        }
        
        .transaction-info p {
            font-size: 11px;
            margin: 5px 0;
            color: #78716c;
        }
        
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
        }
        
        .stamp {
            margin-top: 40px;
            text-align: center;
            padding: 20px;
            border: 3px solid #10b981;
            border-radius: 10px;
            background-color: #f0fdf4;
        }
        
        .stamp h2 {
            color: #10b981;
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .stamp p {
            color: #047857;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="watermark">LINEA SIMPLE</div>
    
    <div class="header">
        <h1>Línea Simple</h1>
        <p class="subtitle">COMPROBANTE DE PAGO</p>
        <p>Nº {{ str_pad($pago->id_pago, 8, '0', STR_PAD_LEFT) }}</p>
    </div>

    <div class="info-grid">
        <div class="info-column">
            <div class="info-box">
                <h3>Cliente</h3>
                <p><strong>Nombre:</strong> {{ $cliente->nombre }}</p>
                <p><strong>Email:</strong> {{ $cliente->email }}</p>
                @if($cliente->telefono)
                <p><strong>Teléfono:</strong> {{ $cliente->telefono }}</p>
                @endif
                @if($cliente->direccion)
                <p><strong>Dirección:</strong> {{ $cliente->direccion }}</p>
                @endif
            </div>
        </div>
        
        <div class="info-column">
            <div class="info-box">
                <h3>Información del Trabajo</h3>
                <p><strong>Título:</strong> {{ $trabajo->titulo }}</p>
                @if($trabajo->servicio)
                <p><strong>Servicio:</strong> {{ $trabajo->servicio->nombre }}</p>
                @endif
                <p><strong>Fecha Solicitud:</strong> {{ $trabajo->fecha_solicitud->format('d/m/Y') }}</p>
                @if($trabajo->fecha_fin)
                <p><strong>Fecha Finalización:</strong> {{ $trabajo->fecha_fin->format('d/m/Y') }}</p>
                @endif
            </div>
        </div>
    </div>

    <div class="payment-details">
        <h3>Detalles del Pago</h3>
        
        <div class="payment-row">
            <span class="label">Fecha de Pago:</span>
            <span class="value">{{ $pago->fecha->format('d/m/Y H:i') }}</span>
        </div>
        
        <div class="payment-row">
            <span class="label">Tipo de Pago:</span>
            <span class="value">{{ $pago->tipo_pago === 'CONTADO' ? 'Al Contado' : 'A Crédito' }}</span>
        </div>
        
        <div class="payment-row">
            <span class="label">Método de Pago:</span>
            <span class="value">
                @if($pago->metodo_pago === 'QR')
                    Código QR (PagoFácil)
                @elseif($pago->metodo_pago === 'TRANSFERENCIA')
                    Transferencia Bancaria
                @elseif($pago->metodo_pago === 'EFECTIVO')
                    Efectivo
                @else
                    {{ $pago->metodo_pago }}
                @endif
            </span>
        </div>
        
        <div class="payment-row">
            <span class="label">Estado:</span>
            <span class="value">
                <span class="status-badge">{{ $pago->estado }}</span>
            </span>
        </div>
        
        @if($pago->tipo_pago === 'CREDITO')
        <div class="payment-row">
            <span class="label">Número de Cuotas:</span>
            <span class="value">{{ $pago->numero_cuotas }} cuotas de Bs. {{ number_format($pago->monto_cuota, 2) }}</span>
        </div>
        @endif
        
        <div class="payment-row total">
            <span>MONTO TOTAL PAGADO:</span>
            <span>Bs. {{ number_format($pago->monto, 2) }}</span>
        </div>
    </div>

    @if($pago->tipo_pago === 'CREDITO' && $pago->cuotas && $pago->cuotas->count() > 0)
    <div style="margin: 25px 0;">
        <h3 style="color: #10b981; margin-bottom: 15px;">Plan de Cuotas</h3>
        <table>
            <thead>
                <tr>
                    <th class="text-center">Cuota Nº</th>
                    <th>Fecha Vencimiento</th>
                    <th class="text-right">Monto</th>
                    <th class="text-center">Estado</th>
                    <th>Fecha Pago</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pago->cuotas as $cuota)
                <tr>
                    <td class="text-center">{{ $cuota->numero_cuota }}</td>
                    <td>{{ $cuota->fecha_vencimiento->format('d/m/Y') }}</td>
                    <td class="text-right">Bs. {{ number_format($cuota->monto, 2) }}</td>
                    <td class="text-center">
                        <span class="status-badge status-{{ strtolower($cuota->estado) }}">
                            {{ $cuota->estado }}
                        </span>
                    </td>
                    <td>{{ $cuota->fecha_pago ? $cuota->fecha_pago->format('d/m/Y') : '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($pago->referencia_pasarela)
    <div class="transaction-info">
        <h4>Información de la Transacción</h4>
        <p><strong>Referencia PagoFácil:</strong> {{ $pago->referencia_pasarela }}</p>
        @if($pago->datos_pasarela)
            @foreach($pago->datos_pasarela as $key => $value)
                @if(!in_array($key, ['qr_image', 'qr_url']))
                <p><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</p>
                @endif
            @endforeach
        @endif
    </div>
    @endif

    @if($presupuesto)
    <div style="margin: 25px 0; padding: 15px; background-color: #f8fafc; border-radius: 5px;">
        <h4 style="color: #2563eb; margin-bottom: 10px;">Resumen del Presupuesto</h4>
        <p style="font-size: 11px; margin: 5px 0;">
            <strong>Cotización Nº:</strong> #{{ str_pad($presupuesto->id_presupuesto, 6, '0', STR_PAD_LEFT) }}
        </p>
        <p style="font-size: 11px; margin: 5px 0;">
            <strong>Monto Presupuestado:</strong> Bs. {{ number_format($presupuesto->monto_total, 2) }}
        </p>
    </div>
    @endif

    <div class="stamp">
        <h2>✓ PAGO COMPLETADO</h2>
        <p>Documento válido como comprobante de pago</p>
        <p style="margin-top: 10px;">Gracias por su preferencia</p>
    </div>

    <div class="footer">
        <p>Línea Simple - Sistema de Gestión de Trabajos</p>
        <p>Comprobante generado el {{ now()->format('d/m/Y H:i') }}</p>
        <p>Este documento tiene validez legal como comprobante de pago</p>
        <p style="margin-top: 10px;">Para consultas: contacto@lineasimple.com</p>
    </div>
</body>
</html>
