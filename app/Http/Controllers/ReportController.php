<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Main dashboard with KPIs and charts
     */
    public function dashboard(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $kpis = $this->reportService->getMonthlyKPIs($month, $year);
        $salesTrend = $this->reportService->getSalesTrend($year);
        $topServices = $this->reportService->getTopServices(5);

        return Inertia::render('Admin/Reports/Dashboard', [
            'kpis' => $kpis,
            'salesTrend' => $salesTrend,
            'topServices' => $topServices,
            'currentMonth' => (int) $month,
            'currentYear' => (int) $year,
        ]);
    }

    /**
     * Sales by month report
     */
    public function salesByMonth(Request $request, $year = null)
    {
        $year = $year ?? now()->year;
        $salesTrend = $this->reportService->getSalesTrend($year);

        return Inertia::render('Admin/Reports/Sales', [
            'salesTrend' => $salesTrend,
            'year' => (int) $year,
        ]);
    }

    /**
     * Jobs profitability analysis
     */
    public function jobsProfitability(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $profitability = $this->reportService->getJobsProfitability($startDate, $endDate);

        return Inertia::render('Admin/Reports/Profitability', [
            'profitability' => $profitability,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    /**
     * Clients with outstanding debt
     */
    public function clientsWithDebt(Request $request)
    {
        $perPage = $request->input('per_page', 5);
        $clients = $this->reportService->getClientsWithDebt($perPage);

        return Inertia::render('Admin/Reports/Clients', [
            'clients' => $clients,
        ]);
    }

    /**
     * Inventory consumption report
     */
    public function inventoryConsumption(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $perPage = $request->input('per_page', 5);

        $consumption = $this->reportService->getInventoryConsumption($startDate, $endDate, $perPage);

        return Inertia::render('Admin/Reports/Inventory', [
            'consumption' => $consumption,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    /**
     * Export clients with debt report to PDF
     */
    public function exportClientsPdf()
    {
        $clients = $this->reportService->getClientsWithDebt(null); // Get all without pagination
        
        // Calculate totals
        $totalDebt = is_array($clients) ? collect($clients)->sum('total_debt') : $clients->sum('total_debt');

        $pdf = \PDF::loadView('pdf.reporte-clientes', [
            'clients' => $clients,
            'totalDebt' => $totalDebt,
        ]);

        $fileName = "Reporte-Clientes-Deuda-" . now()->format('Ymd') . ".pdf";
        return $pdf->download($fileName);
    }

    /**
     * Export inventory consumption report to PDF
     */
    public function exportInventoryPdf(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        
        $consumption = $this->reportService->getInventoryConsumption($startDate, $endDate, null); // Get all without pagination
        
        // Calculate totals
        $totalQuantity = is_array($consumption) ? collect($consumption)->sum('total_consumed') : $consumption->sum('total_consumed');
        $totalValue = is_array($consumption) ? collect($consumption)->sum('total_value') : $consumption->sum('total_value');

        $pdf = \PDF::loadView('pdf.reporte-inventario', [
            'consumption' => $consumption,
            'totalQuantity' => $totalQuantity,
            'totalValue' => $totalValue,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);

        $fileName = "Reporte-Inventario-" . now()->format('Ymd') . ".pdf";
        return $pdf->download($fileName);
    }

    /**
     * Export profitability report to PDF
     */
    public function exportProfitabilityPdf(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        
        $profitability = $this->reportService->getJobsProfitability($startDate, $endDate);
        
        // Calculate totals
        $totalIncome = is_array($profitability) ? collect($profitability)->sum('revenue') : $profitability->sum('revenue');
        $totalCosts = is_array($profitability) ? collect($profitability)->sum('expenses') : $profitability->sum('expenses');
        $totalProfit = is_array($profitability) ? collect($profitability)->sum('profit') : $profitability->sum('profit');

        $pdf = \PDF::loadView('pdf.reporte-rentabilidad', [
            'profitability' => $profitability,
            'totalIncome' => $totalIncome,
            'totalCosts' => $totalCosts,
            'totalProfit' => $totalProfit,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);

        $fileName = "Reporte-Rentabilidad-" . now()->format('Ymd') . ".pdf";
        return $pdf->download($fileName);
    }
}
