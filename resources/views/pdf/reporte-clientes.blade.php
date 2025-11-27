<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Clientes con Deuda</title>
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
            color: rgba(239, 68, 68, 0.05);
            font-weight: bold;
            z-index: -1;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #ef4444;
        }
        
        .header h1 {
            color: #ef4444;
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .header .subtitle {
            color: #666;
            font-size: 14px;
        }
        
        .info-section {
            margin-bottom: 25px;
            background-color: #fef2f2;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #ef4444;
        }
        
        .info-section h3 {
            color: #dc2626;
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
            width: 50%;
            padding: 10px;
        }
        
        .stat-label {
            font-size: 11px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .stat-value {
            font-size: 20px;
            font-weight: bold;
            color: #dc2626;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        thead {
            background-color: #ef4444;
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
        
        .badge-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        .badge-warning {
            background-color: #fef3c7;
            color: #92400e;
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
        <p class="subtitle">REPORTE DE CLIENTES CON DEUDA</p>
        <p>Generado el {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="info-section">
        <h3>📊 Resumen General</h3>
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-label">Total Clientes con Deuda:</div>
                <div class="stat-value">{{ count($clients) }}</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Deuda Total:</div>
                <div class="stat-value">Bs {{ number_format($totalDebt, 2) }}</div>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Email</th>
                <th class="text-right">Deuda Total</th>
                <th class="text-right">Cuotas Vencidas</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clients as $client)
            <tr>
                <td>{{ $client['name'] }}</td>
                <td>{{ $client['email'] }}</td>
                <td class="text-right">
                    <strong>Bs {{ number_format($client['total_debt'], 2) }}</strong>
                </td>
                <td class="text-right">
                    @if($client['overdue_payments'] > 0)
                        <span class="badge badge-danger">{{ $client['overdue_payments'] }}</span>
                    @else
                        <span class="badge badge-warning">0</span>
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
