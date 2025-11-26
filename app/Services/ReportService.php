<?php

namespace App\Services;

use App\Models\Trabajo;
use App\Models\Pago;
use App\Models\GastoOperativo;
use App\Models\Material;
use App\Models\Presupuesto;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportService
{
    /**
     * Get monthly KPIs for dashboard
     */
    public function getMonthlyKPIs($month = null, $year = null)
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;
        
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        // Total sales (approved budgets)
        $totalSales = Presupuesto::where('estado', 'APROBADO')
            ->whereBetween('fecha_respuesta', [$startDate, $endDate])
            ->sum('monto_total');

        // Completed jobs
        $completedJobs = Trabajo::where('estado', 'FINALIZADO')
            ->whereBetween('fecha_fin', [$startDate, $endDate])
            ->count();

        // Total expenses
        $totalExpenses = GastoOperativo::whereBetween('fecha', [$startDate, $endDate])
            ->sum('monto');

        // Net profit
        $netProfit = $totalSales - $totalExpenses;

        // Active clients (clients with jobs this month)
        $activeClients = Trabajo::whereBetween('fecha_solicitud', [$startDate, $endDate])
            ->distinct('id_cliente')
            ->count('id_cliente');

        // Jobs by status
        $jobsByStatus = Trabajo::select('estado', DB::raw('count(*) as total'))
            ->whereBetween('fecha_solicitud', [$startDate, $endDate])
            ->groupBy('estado')
            ->get()
            ->pluck('total', 'estado');

        return [
            'total_sales' => (float) $totalSales,
            'completed_jobs' => $completedJobs,
            'total_expenses' => (float) $totalExpenses,
            'net_profit' => (float) $netProfit,
            'active_clients' => $activeClients,
            'jobs_by_status' => $jobsByStatus,
            'period' => [
                'month' => $month,
                'year' => $year,
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
            ],
        ];
    }

    /**
     * Get sales trend for the year
     */
    public function getSalesTrend($year = null)
    {
        $year = $year ?? now()->year;
        
        $salesByMonth = Presupuesto::select(
                DB::raw('EXTRACT(MONTH FROM fecha_respuesta) as month'),
                DB::raw('SUM(monto_total) as total')
            )
            ->where('estado', 'APROBADO')
            ->whereYear('fecha_respuesta', $year)
            ->groupBy(DB::raw('EXTRACT(MONTH FROM fecha_respuesta)'))
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month');

        // Fill missing months with 0
        $monthlyData = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyData[$i] = (float) ($salesByMonth[$i] ?? 0);
        }

        return [
            'year' => $year,
            'months' => array_keys($monthlyData),
            'sales' => array_values($monthlyData),
        ];
    }

    /**
     * Calculate profitability for a specific job
     */
    public function calculateJobProfitability($jobId)
    {
        $trabajo = Trabajo::with(['presupuestos.detalles.material', 'cliente'])->findOrFail($jobId);
        
        $presupuesto = $trabajo->presupuestos()->where('estado', 'APROBADO')->first();
        
        if (!$presupuesto) {
            return null;
        }

        // Calcular costos de materiales
        $costoMateriales = $presupuesto->detalles->sum(function ($detalle) {
            return $detalle->cantidad * ($detalle->material->precio_unitario ?? 0);
        });
        
        $revenue = (float) $presupuesto->monto_total;
        $expenses = (float) ($costoMateriales + ($presupuesto->mano_obra ?? 0) + ($presupuesto->otros_costos ?? 0));
        $profit = $revenue - $expenses;
        $margin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;

        return [
            'job_id' => $trabajo->id_trabajo,
            'job_title' => $trabajo->titulo,
            'client' => $trabajo->cliente->nombre,
            'revenue' => $revenue,
            'expenses' => $expenses,
            'profit' => $profit,
            'margin_percentage' => round($margin, 2),
            'status' => $trabajo->estado,
        ];
    }

    /**
     * Get profitability for all jobs in a date range
     */
    public function getJobsProfitability($startDate = null, $endDate = null)
    {
        $startDate = $startDate ?? now()->subMonths(3)->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();

        $trabajos = Trabajo::with(['cliente', 'presupuestos.detalles.material'])
            ->whereHas('presupuestos', function ($query) {
                $query->where('estado', 'APROBADO');
            })
            ->whereBetween('fecha_solicitud', [$startDate, $endDate])
            ->get();

        $profitability = [];
        foreach ($trabajos as $trabajo) {
            $data = $this->calculateJobProfitability($trabajo->id_trabajo);
            if ($data) {
                $profitability[] = $data;
            }
        }

        return $profitability;
    }

    /**
     * Get clients with outstanding debt
     */
    public function getClientsWithDebt($perPage = 5)
    {
        $clients = User::where('rol', 'CLIENTE')
            ->with(['trabajos.pagos.cuotas'])
            ->get();

        $clientsWithDebt = [];
        $globalTotalDebt = 0;
        $globalOverduePayments = 0;

        foreach ($clients as $client) {
            $totalDebt = 0;
            $overduePayments = 0;
            $jobsWithDebt = [];

            foreach ($client->trabajos as $trabajo) {
                foreach ($trabajo->pagos as $pago) {
                    if ($pago->tipo_pago === 'CREDITO') {
                        $pendingCuotas = $pago->cuotas()
                            ->where('estado', 'PENDIENTE')
                            ->orWhere('estado', 'VENCIDA')
                            ->get();

                        foreach ($pendingCuotas as $cuota) {
                            $totalDebt += $cuota->monto;
                            
                            if ($cuota->estado === 'VENCIDA' || 
                                Carbon::parse($cuota->fecha_vencimiento)->isPast()) {
                                $overduePayments++;
                            }
                        }

                        if ($pendingCuotas->count() > 0) {
                            $jobsWithDebt[] = $trabajo->titulo;
                        }
                    }
                }
            }

            if ($totalDebt > 0) {
                $clientsWithDebt[] = [
                    'client_id' => $client->id_usuario,
                    'name' => $client->nombre,
                    'email' => $client->email,
                    'total_debt' => (float) $totalDebt,
                    'overdue_payments' => $overduePayments,
                    'jobs_with_debt' => $jobsWithDebt,
                ];
                $globalTotalDebt += $totalDebt;
                $globalOverduePayments += $overduePayments;
            }
        }

        // Convert to collection and paginate
        $collection = collect($clientsWithDebt);
        $currentPage = request()->get('page', 1);
        $items = $collection->forPage($currentPage, $perPage);
        
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $collection->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Add global totals to paginator
        $paginator->globalTotalDebt = $globalTotalDebt;
        $paginator->globalOverduePayments = $globalOverduePayments;

        return $paginator;
    }

    /**
     * Get inventory consumption for a date range
     */
    public function getInventoryConsumption($startDate = null, $endDate = null, $perPage = 5)
    {
        $startDate = $startDate ?? now()->subMonths(1)->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();

        $consumption = DB::table('presupuesto_detalle_material')
            ->join('presupuestos', 'presupuesto_detalle_material.id_presupuesto', '=', 'presupuestos.id_presupuesto')
            ->join('materiales', 'presupuesto_detalle_material.id_material', '=', 'materiales.id_material')
            ->where('presupuestos.estado', 'APROBADO')
            ->whereBetween('presupuestos.fecha_respuesta', [$startDate, $endDate])
            ->select(
                'materiales.id_material',
                'materiales.nombre',
                'materiales.unidad_medida',
                'materiales.precio_unitario',
                DB::raw('SUM(presupuesto_detalle_material.cantidad) as total_consumed'),
                DB::raw('SUM(presupuesto_detalle_material.subtotal) as total_value')
            )
            ->groupBy('materiales.id_material', 'materiales.nombre', 'materiales.unidad_medida', 'materiales.precio_unitario')
            ->orderBy('total_consumed', 'desc')
            ->get();

        $mapped = $consumption->map(function ($item) {
            return [
                'material_id' => $item->id_material,
                'name' => $item->nombre,
                'unit' => $item->unidad_medida,
                'unit_price' => (float) $item->precio_unitario,
                'total_consumed' => (float) $item->total_consumed,
                'total_value' => (float) $item->total_value,
            ];
        });

        // Calculate global totals
        $globalTotalQuantity = $mapped->sum('total_consumed');
        $globalTotalValue = $mapped->sum('total_value');

        // Convert to collection and paginate
        $currentPage = request()->get('page', 1);
        $items = $mapped->forPage($currentPage, $perPage);
        
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $mapped->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Add global totals to paginator
        $paginator->globalTotalQuantity = $globalTotalQuantity;
        $paginator->globalTotalValue = $globalTotalValue;

        return $paginator;
    }

    /**
     * Get top requested services
     */
    public function getTopServices($limit = 5)
    {
        return Trabajo::select('servicios.nombre', DB::raw('count(*) as total'))
            ->join('servicios', 'trabajos.id_servicio', '=', 'servicios.id_servicio')
            ->whereNotNull('trabajos.id_servicio')
            ->groupBy('servicios.id_servicio', 'servicios.nombre')
            ->orderBy('total', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'service' => $item->nombre,
                    'count' => $item->total,
                ];
            });
    }
}
