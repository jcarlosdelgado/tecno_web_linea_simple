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
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            padding: 30px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 20px;
        }
        
        .header h1 {
            color: #2563eb;
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .header .subtitle {
            color: #64748b;
            font-size: 14px;
        }
        
        .info-section {
            margin-bottom: 25px;
        }
        
        .info-section h2 {
            background-color: #f1f5f9;
            color: #1e293b;
            padding: 8px 12px;
            font-size: 14px;
            border-left: 4px solid #2563eb;
            margin-bottom: 12px;
        }
        
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-label {
            display: table-cell;
            font-weight: bold;
            padding: 6px 12px;
            width: 35%;
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .info-value {
            display: table-cell;
            padding: 6px 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .amount-box {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 25px 0;
        }
        
        .amount-box .label {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 8px;
        }
        
        .amount-box .amount {
            font-size: 36px;
            font-weight: bold;
        }
        
        .cuotas-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .cuotas-table thead {
            background-color: #2563eb;
            color: white;
        }
        
        .cuotas-table th,
        .cuotas-table td {
            padding: 10px;
            text-align: left;
            border: 1px solid #e2e8f0;
        }
        
        .cuotas-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        
        .status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .status-pagada {
            background-color: #dcfce7;
            color: #166534;
        }
        
        .status-pendiente {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .status-pagado {
            background-color: #dcfce7;
            color: #166534;
        }
        
        .status-en-revision {
            background-color: #dbeafe;
            color: #1e40af;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e2e8f0;
            text-align: center;
            color: #64748b;
            font-size: 11px;
        }
        
        .signature-section {
            margin-top: 60px;
            display: table;
            width: 100%;
        }
        
        .signature-box {
            display: table-cell;
            width: 45%;
            text-align: center;
            padding: 0 20px;
        }
        
        .signature-line {
            border-top: 2px solid #333;
            margin-top: 60px;
            padding-top: 8px;
            font-weight: bold;
        }
        
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            color: rgba(37, 99, 235, 0.05);
            font-weight: bold;
            z-index: -1;
        }
    </style>
</head>
<body>
    <div class="watermark">LINEA SIMPLE</div>

    <div class="header">
        <h1>COMPROBANTE DE PAGO</h1>
        <div class="subtitle">Línea Simple - Sistema de Gestión de Trabajos</div>
    </div>

    <!-- Información del Comprobante -->
    <div class="info-section">
        <h2>📄 Información del Comprobante</h2>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Nº de Pago:</div>
                <div class="info-value">#{{ str_pad($pago->id_pago, 6, '0', STR_PAD_LEFT) }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Fecha de Emisión:</div>
                <div class="info-value">{{ now()->format('d/m/Y H:i') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Fecha de Pago:</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($pago->fecha)->format('d/m/Y') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Estado:</div>
                <div class="info-value">
                    <span class="status status-{{ strtolower($pago->estado) }}">
                        {{ $pago->estado }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Información del Cliente -->
    <div class="info-section">
        <h2>👤 Datos del Cliente</h2>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Nombre:</div>
                <div class="info-value">{{ $cliente->nombre }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Email:</div>
                <div class="info-value">{{ $cliente->email }}</div>
            </div>
            @if($cliente->telefono)
            <div class="info-row">
                <div class="info-label">Teléfono:</div>
                <div class="info-value">{{ $cliente->telefono }}</div>
            </div>
            @endif
        </div>
    </div>

    <!-- Información del Trabajo -->
    <div class="info-section">
        <h2>🛠️ Detalles del Trabajo</h2>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Nº de Trabajo:</div>
                <div class="info-value">#{{ str_pad($trabajo->id_trabajo, 6, '0', STR_PAD_LEFT) }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Título:</div>
                <div class="info-value">{{ $trabajo->titulo }}</div>
            </div>
            @if($trabajo->servicio)
            <div class="info-row">
                <div class="info-label">Servicio:</div>
                <div class="info-value">{{ $trabajo->servicio->nombre }}</div>
            </div>
            @endif
            <div class="info-row">
                <div class="info-label">Descripción:</div>
                <div class="info-value">{{ Str::limit($trabajo->descripcion, 100) }}</div>
            </div>
        </div>
    </div>

    <!-- Información del Pago -->
    <div class="info-section">
        <h2>💳 Detalles del Pago</h2>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Tipo de Pago:</div>
                <div class="info-value">{{ $pago->tipo_pago === 'CONTADO' ? 'Al Contado' : 'A Crédito' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Método de Pago:</div>
                <div class="info-value">{{ $pago->metodo_pago }}</div>
            </div>
            @if($pago->tipo_pago === 'CREDITO')
            <div class="info-row">
                <div class="info-label">Número de Cuotas:</div>
                <div class="info-value">{{ $pago->numero_cuotas }} cuotas</div>
            </div>
            <div class="info-row">
                <div class="info-label">Monto por Cuota:</div>
                <div class="info-value">Bs. {{ number_format($pago->monto_cuota, 2) }}</div>
            </div>
            @endif
        </div>
    </div>

    <!-- Monto Total -->
    <div class="amount-box">
        <div class="label">MONTO TOTAL</div>
        <div class="amount">Bs. {{ number_format($pago->monto, 2) }}</div>
    </div>

    <!-- Tabla de Cuotas (si es a crédito) -->
    @if($pago->tipo_pago === 'CREDITO' && $pago->cuotas->count() > 0)
    <div class="info-section">
        <h2>📅 Plan de Cuotas</h2>
        <table class="cuotas-table">
            <thead>
                <tr>
                    <th style="width: 15%; text-align: center;">Cuota</th>
                    <th style="width: 25%;">Monto</th>
                    <th style="width: 25%;">Vencimiento</th>
                    <th style="width: 25%;">Fecha Pago</th>
                    <th style="width: 10%; text-align: center;">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pago->cuotas as $cuota)
                <tr>
                    <td style="text-align: center; font-weight: bold;">{{ $cuota->numero_cuota }}/{{ $pago->numero_cuotas }}</td>
                    <td>Bs. {{ number_format($cuota->monto, 2) }}</td>
                    <td>{{ \Carbon\Carbon::parse($cuota->fecha_vencimiento)->format('d/m/Y') }}</td>
                    <td>{{ $cuota->fecha_pago ? \Carbon\Carbon::parse($cuota->fecha_pago)->format('d/m/Y') : '-' }}</td>
                    <td style="text-align: center;">
                        <span class="status status-{{ strtolower($cuota->estado) }}">
                            {{ $cuota->estado }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Firmas -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">Firma del Cliente</div>
            <div>{{ $cliente->nombre }}</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">Firma Autorizada</div>
            <div>Línea Simple</div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p><strong>Línea Simple</strong> - Sistema de Gestión de Trabajos de Construcción</p>
        <p>Este es un comprobante válido generado electrónicamente el {{ now()->format('d/m/Y \a \l\a\s H:i') }}</p>
        <p>Para cualquier consulta, contáctenos a través de nuestros canales oficiales.</p>
    </div>
</body>
</html>
