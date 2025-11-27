<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Consumo de Inventario</title>
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
        
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            color: rgba(59, 130, 246, 0.05);
            font-weight: bold;
            z-index: -1;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #3b82f6;
        }
        
        .header h1 {
            color: #3b82f6;
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .header .subtitle {
            color: #666;
            font-size: 14px;
        }
        
        .info-section {
            margin-bottom: 25px;
            background-color: #eff6ff;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #3b82f6;
        }
        
        .info-section h3 {
            color: #1e40af;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .stats-grid {
            display: table;
            width: 100%;
            margin-top: 10px;
        }
        
        .stat-item {
            display: table-cell;
            width: 33.33%;
            padding: 10px;
        }
        
        .stat-label {
            font-size: 11px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #1e40af;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        thead {
            background-color: #3b82f6;
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
        
        .text-right {
            text-align: right;
        }
        
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
        }
        
        .total-row {
            font-weight: bold;
            background-color: #dbeafe !important;
            border-top: 2px solid #3b82f6;
        }
    </style>
</head>
<body>
    <div class="watermark">LINEA SIMPLE</div>

    <div class="header">
        <h1>Línea Simple</h1>
        <p class="subtitle">REPORTE DE CONSUMO DE INVENTARIO</p>
        <p>Período: {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d/m/Y') : 'Desde inicio' }} - {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d/m/Y') : 'Hasta hoy' }}</p>
        <p>Generado el {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="info-section">
        <h3>📊 Resumen del Período</h3>
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-label">Materiales Consumidos:</div>
                <div class="stat-value">{{ count($consumption) }}</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Cantidad Total:</div>
                <div class="stat-value">{{ number_format($totalQuantity, 2) }}</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Valor Total:</div>
                <div class="stat-value">Bs {{ number_format($totalValue, 2) }}</div>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Material</th>
                <th>Unidad</th>
                <th class="text-right">Cantidad</th>
                <th class="text-right">Precio Unit.</th>
                <th class="text-right">Valor Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($consumption as $item)
            <tr>
                <td>{{ $item['name'] }}</td>
                <td>{{ $item['unit'] }}</td>
                <td class="text-right">{{ number_format($item['total_consumed'], 2) }}</td>
                <td class="text-right">Bs {{ number_format($item['unit_price'], 2) }}</td>
                <td class="text-right">Bs {{ number_format($item['total_value'], 2) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2"><strong>TOTALES</strong></td>
                <td class="text-right"><strong>{{ number_format($totalQuantity, 2) }}</strong></td>
                <td class="text-right">-</td>
                <td class="text-right"><strong>Bs {{ number_format($totalValue, 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Línea Simple - Sistema de Gestión de Trabajos</p>
        <p>Este reporte es confidencial y de uso interno</p>
    </div>
</body>
</html>
