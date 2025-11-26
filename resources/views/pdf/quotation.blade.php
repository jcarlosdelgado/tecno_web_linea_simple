<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotización #{{ $presupuesto->id_presupuesto }}</title>
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
            border-bottom: 3px solid #2563eb;
        }
        
        .header h1 {
            color: #2563eb;
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .header p {
            color: #666;
            font-size: 14px;
        }
        
        .info-section {
            margin-bottom: 25px;
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
            background-color: #f8fafc;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #2563eb;
        }
        
        .info-box h3 {
            color: #2563eb;
            font-size: 14px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        
        .info-box p {
            margin: 5px 0;
            font-size: 12px;
        }
        
        .info-box strong {
            color: #1e40af;
        }
        
        .table-container {
            margin: 25px 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        thead {
            background-color: #2563eb;
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
        
        tbody tr:hover {
            background-color: #f3f4f6;
        }
        
        .text-right {
            text-align: right;
        }
        
        .summary-box {
            float: right;
            width: 300px;
            margin-top: 20px;
            background-color: #f8fafc;
            padding: 20px;
            border-radius: 5px;
            border: 2px solid #2563eb;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 13px;
        }
        
        .summary-row.total {
            border-top: 2px solid #2563eb;
            margin-top: 10px;
            padding-top: 15px;
            font-size: 16px;
            font-weight: bold;
            color: #2563eb;
        }
        
        .notes {
            clear: both;
            margin-top: 40px;
            padding: 15px;
            background-color: #fffbeb;
            border-left: 4px solid #f59e0b;
            border-radius: 5px;
        }
        
        .notes h4 {
            color: #d97706;
            margin-bottom: 10px;
            font-size: 13px;
        }
        
        .notes p {
            font-size: 11px;
            line-height: 1.6;
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
        
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .status-approved {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .status-rejected {
            background-color: #fee2e2;
            color: #991b1b;
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
        <h1>Línea Simple</h1>
        <p>Cotización de Servicio</p>
    </div>

    <div class="info-grid">
        <div class="info-column">
            <div class="info-box">
                <h3>Información del Cliente</h3>
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
                <h3>Información del Presupuesto</h3>
                <p><strong>Nº Cotización:</strong> #{{ str_pad($presupuesto->id_presupuesto, 6, '0', STR_PAD_LEFT) }}</p>
                <p><strong>Fecha Emisión:</strong> {{ $presupuesto->fecha_emision ? $presupuesto->fecha_emision->format('d/m/Y') : 'N/A' }}</p>
                <p><strong>Válido hasta:</strong> {{ $presupuesto->fecha_validez ? $presupuesto->fecha_validez->format('d/m/Y') : 'N/A' }}</p>
                <p><strong>Estado:</strong> 
                    <span class="status-badge status-{{ strtolower($presupuesto->estado) }}">
                        {{ $presupuesto->estado }}
                    </span>
                </p>
            </div>
        </div>
    </div>

    <div class="info-section">
        <div class="info-box">
            <h3>Detalles del Trabajo</h3>
            <p><strong>Título:</strong> {{ $trabajo->titulo }}</p>
            @if($trabajo->servicio)
            <p><strong>Servicio:</strong> {{ $trabajo->servicio->nombre }}</p>
            @endif
            <p><strong>Descripción:</strong> {{ $trabajo->descripcion }}</p>
            @if($trabajo->medidas)
            <p><strong>Medidas:</strong> 
                @if(isset($trabajo->medidas['ancho'])) Ancho: {{ $trabajo->medidas['ancho'] }} @endif
                @if(isset($trabajo->medidas['alto'])) Alto: {{ $trabajo->medidas['alto'] }} @endif
                @if(isset($trabajo->medidas['profundidad'])) Prof: {{ $trabajo->medidas['profundidad'] }} @endif
            </p>
            @endif
            @if($trabajo->cantidad)
            <p><strong>Cantidad:</strong> {{ $trabajo->cantidad }} unidades</p>
            @endif
            @if($trabajo->colores)
            <p><strong>Colores:</strong> {{ $trabajo->colores }}</p>
            @endif
        </div>
    </div>

    @if($presupuesto->detalles && $presupuesto->detalles->count() > 0)
    <div class="table-container">
        <h3 style="margin-bottom: 15px; color: #2563eb;">Detalle de Materiales</h3>
        <table>
            <thead>
                <tr>
                    <th>Material</th>
                    <th>Unidad</th>
                    <th class="text-right">Cantidad</th>
                    <th class="text-right">Precio Unit.</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($presupuesto->detalles as $detalle)
                <tr>
                    <td>{{ $detalle->material->nombre }}</td>
                    <td>{{ $detalle->material->unidad_medida }}</td>
                    <td class="text-right">{{ number_format($detalle->cantidad, 2) }}</td>
                    <td class="text-right">Bs. {{ number_format($detalle->precio_unitario, 2) }}</td>
                    <td class="text-right">Bs. {{ number_format($detalle->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="summary-box">
        @if($presupuesto->detalles && $presupuesto->detalles->count() > 0)
        <div class="summary-row">
            <span>Materiales:</span>
            <span>Bs. {{ number_format($presupuesto->detalles->sum('subtotal'), 2) }}</span>
        </div>
        @endif
        <div class="summary-row">
            <span>Mano de Obra:</span>
            <span>Bs. {{ number_format($presupuesto->mano_obra, 2) }}</span>
        </div>
        @if($presupuesto->otros_costos > 0)
        <div class="summary-row">
            <span>Otros Costos:</span>
            <span>Bs. {{ number_format($presupuesto->otros_costos, 2) }}</span>
        </div>
        @endif
        <div class="summary-row total">
            <span>TOTAL:</span>
            <span>Bs. {{ number_format($presupuesto->monto_total, 2) }}</span>
        </div>
    </div>

    @if($presupuesto->notas || $presupuesto->notas_adicionales)
    <div class="notes">
        <h4>Notas y Condiciones</h4>
        @if($presupuesto->notas)
        <p><strong>Notas:</strong> {{ $presupuesto->notas }}</p>
        @endif
        @if($presupuesto->notas_adicionales)
        <p><strong>Información adicional:</strong> {{ $presupuesto->notas_adicionales }}</p>
        @endif
    </div>
    @endif

    <div class="footer">
        <p>Línea Simple - Sistema de Gestión de Trabajos</p>
        <p>Documento generado el {{ now()->format('d/m/Y H:i') }}</p>
        <p>Este presupuesto es válido por 30 días desde la fecha de emisión</p>
    </div>
</body>
</html>
