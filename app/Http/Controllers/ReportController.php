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
}
