<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Rentabilidad</title>
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
            color: rgba(16, 185, 129, 0.05);
            font-weight: bold;
            z-index: -1;
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
            font-size: 14px;
        }
        
        .info-section {
            margin-bottom: 25px;
            background-color: #d1fae5;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #10b981;
        }
        
        .info-section h3 {
            color: #047857;
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
            width: 25%;
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
            color: #047857;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
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
        
        .text-right {
            text-align: right;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
        }
        
        .badge-success {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .badge-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="watermark">LINEA SIMPLE</div>

    <div class="header">
        <h1>Línea Simple</h1>
        <p class="subtitle">ANÁLISIS DE RENTABILIDAD</p>
        <p>Período: {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d/m/Y') : 'Desde inicio' }} - {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d/m/Y') : 'Hasta hoy' }}</p>
        <p>Generado el {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="info-section">
        <h3>📊 Resumen Financiero</h3>
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-label">Trabajos Analizados:</div>
                <div class="stat-value">{{ count($profitability) }}</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Ingresos Totales:</div>
                <div class="stat-value">Bs {{ number_format($totalIncome, 2) }}</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Costos Totales:</div>
                <div class="stat-value">Bs {{ number_format($totalCosts, 2) }}</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Utilidad Total:</div>
                <div class="stat-value">Bs {{ number_format($totalProfit, 2) }}</div>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Trabajo</th>
                <th class="text-right">Ingresos</th>
                <th class="text-right">Costos</th>
                <th class="text-right">Utilidad</th>
                <th class="text-right">Margen %</th>
            </tr>
        </thead>
        <tbody>
            @foreach($profitability as $item)
            <tr>
                <td>{{ $item['job_title'] }}</td>
                <td class="text-right">Bs {{ number_format($item['revenue'], 2) }}</td>
                <td class="text-right">Bs {{ number_format($item['expenses'], 2) }}</td>
                <td class="text-right">
                    <strong style="color: {{ $item['profit'] >= 0 ? '#047857' : '#dc2626' }}">
                        Bs {{ number_format($item['profit'], 2) }}
                    </strong>
                </td>
                <td class="text-right">
                    @if($item['margin_percentage'] >= 0)
                        <span class="badge badge-success">{{ number_format($item['margin_percentage'], 1) }}%</span>
                    @else
                        <span class="badge badge-danger">{{ number_format($item['margin_percentage'], 1) }}%</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Línea Simple - Sistema de Gestión de Trabajos</p>
        <p>Este reporte es confidencial y de uso interno</p>
    </div>
</body>
</html>
