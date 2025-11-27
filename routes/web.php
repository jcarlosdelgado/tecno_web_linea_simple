<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\WorkerController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\Api\PageVisitController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Public service catalog routes
Route::get('/servicios', [ServicioController::class, 'index'])->name('servicios.index');
Route::get('/servicios/{servicio}', [ServicioController::class, 'show'])->name('servicios.show');

// API Routes
Route::get('/api/page-visits', [PageVisitController::class, 'getVisits']);

Route::middleware(['auth', 'verified', 'cliente'])->group(function () {
    Route::get('/dashboard', [ClientController::class, 'dashboard'])->name('dashboard');
    Route::get('/trabajos/crear', [ClientController::class, 'create'])->name('trabajos.create');
    Route::post('/trabajos', [ClientController::class, 'store'])->name('trabajos.store');
    Route::get('/trabajos/{id}', [ClientController::class, 'show'])->name('trabajos.show');
    Route::post('/presupuestos/{id}/aprobar', [ClientController::class, 'approveBudget'])->name('presupuestos.approve');
    Route::post('/presupuestos/{id}/rechazar', [ClientController::class, 'rejectBudget'])->name('presupuestos.reject');
    
    // Rutas de pago
    Route::get('/pagos/seleccionar/{trabajo}', [PaymentController::class, 'selectMethod'])->name('pagos.select');
    Route::post('/pagos/iniciar/{trabajo}', [PaymentController::class, 'initiate'])->name('pagos.initiate');
    Route::get('/pagos/checkout/{pago}', [PaymentController::class, 'checkout'])->name('pagos.checkout');
    Route::post('/pagos/confirmar/{pago}', [PaymentController::class, 'confirm'])->name('pagos.confirm');
    Route::post('/pagos/generar-qr/{trabajo}', [PaymentController::class, 'generateQR'])->name('pagos.generateQR');
    Route::get('/pagos/estado/{pago}', [PaymentController::class, 'checkPaymentStatus'])->name('pagos.status');
    
    // Rutas de cuotas y pagos
    Route::get('/historial-pagos', [PaymentController::class, 'allInstallments'])->name('pagos.historial');
    Route::get('/pagos/{pago}/cuotas', [PaymentController::class, 'showInstallments'])->name('cuotas.index');
    Route::post('/cuotas/{cuota}/generar-qr', [PaymentController::class, 'generateInstallmentQR'])->name('cuotas.generateQR');
    Route::get('/cuotas/{cuota}/estado', [PaymentController::class, 'checkInstallmentStatus'])->name('cuotas.status');
    
    // Rutas de descarga de comprobantes PDF
    Route::get('/pagos/{pago}/descargar-comprobante', [PaymentController::class, 'downloadComprobante'])->name('pagos.descargar-comprobante');
    Route::get('/cuotas/{cuota}/descargar-comprobante', [PaymentController::class, 'downloadComprobanteCuota'])->name('cuotas.descargar-comprobante');
    
    // Rutas de descarga de PDFs (antiguas - PdfController)
    Route::get('/presupuestos/{id}/pdf', [PdfController::class, 'downloadQuotation'])->name('presupuestos.pdf');
    Route::get('/presupuestos/{id}/ver-pdf', [PdfController::class, 'viewQuotation'])->name('presupuestos.view-pdf');
    Route::get('/pagos/{id}/comprobante', [PdfController::class, 'downloadInvoice'])->name('pagos.comprobante');
    Route::get('/pagos/{id}/ver-comprobante', [PdfController::class, 'viewInvoice'])->name('pagos.view-comprobante');
    
    // Rutas de perfil y historial
    Route::get('/perfil/editar', [ClientController::class, 'editProfile'])->name('perfil.edit');
    Route::put('/perfil', [ClientController::class, 'updateProfile'])->name('perfil.update');
    Route::get('/trabajos/historial', [ClientController::class, 'history'])->name('trabajos.history');
});

// Public callback route for Pago Fácil (no auth required)
Route::post('/api/pagofacil/callback', [PaymentController::class, 'handleCallback'])->name('pagofacil.callback');

// Worker routes
Route::middleware(['auth', 'verified', 'trabajador'])->prefix('trabajador')->name('trabajador.')->group(function () {
    Route::get('/dashboard', [WorkerController::class, 'dashboard'])->name('dashboard');
    Route::get('/trabajos/{id}', [WorkerController::class, 'show'])->name('trabajos.show');
    Route::post('/trabajos/{id}/progreso', [WorkerController::class, 'storeProgress'])->name('progreso.store');
    Route::get('/historial', [WorkerController::class, 'history'])->name('history');
});

Route::middleware(['auth', 'verified', 'propietario'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [OwnerController::class, 'dashboard'])->name('dashboard');
    Route::get('/test', function () {
        return Inertia::render('Admin/Test');
    });
    Route::get('/trabajos/{id}', [OwnerController::class, 'show'])->name('trabajos.show');
    Route::post('/trabajos/{id}/presupuestos', [OwnerController::class, 'storeBudget'])->name('presupuestos.store');
    Route::post('/trabajos/{id}/seguimientos', [OwnerController::class, 'storeTracking'])->name('seguimientos.store');
    Route::post('/trabajos/{id}/asignar-trabajador', [OwnerController::class, 'assignWorker'])->name('trabajos.assign-worker');
    
    // Comprobantes de pago PDF (nuevos - PaymentController)
    Route::get('/pagos/{pago}/descargar-comprobante', [PaymentController::class, 'downloadComprobante'])->name('pagos.descargar-comprobante');
    Route::get('/cuotas/{cuota}/descargar-comprobante', [PaymentController::class, 'downloadComprobanteCuota'])->name('cuotas.descargar-comprobante');
    
    // PDF routes for admin (antiguas - PdfController)
    Route::get('/presupuestos/{id}/pdf', [PdfController::class, 'downloadQuotation'])->name('presupuestos.pdf');
    Route::get('/presupuestos/{id}/ver-pdf', [PdfController::class, 'viewQuotation'])->name('presupuestos.view-pdf');
    Route::get('/pagos/{id}/comprobante', [PdfController::class, 'downloadInvoice'])->name('pagos.comprobante');
    Route::get('/pagos/{id}/ver-comprobante', [PdfController::class, 'viewInvoice'])->name('pagos.view-comprobante');
    
    // User management routes
    Route::resource('usuarios', UserManagementController::class);
    Route::post('usuarios/{id}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('usuarios.toggle-status');
    
    // Roles and permissions management
    Route::resource('roles', RoleController::class);
    Route::get('permisos', [RoleController::class, 'getPermisos'])->name('permisos.index');
    Route::post('usuarios/asignar-rol', [RoleController::class, 'assignToUser'])->name('usuarios.assign-role');
    
    // Payment management routes
    Route::get('/pagos', [PaymentController::class, 'adminIndex'])->name('pagos.index');
    Route::get('/clientes/{id}/pagos', [PaymentController::class, 'clientPayments'])->name('clientes.pagos');
    
    // Admin access to payment details (same as client routes)
    Route::get('/pagos/checkout/{pago}', [PaymentController::class, 'checkout'])->name('pagos.checkout');
    Route::get('/pagos/{pago}/cuotas', [PaymentController::class, 'showInstallments'])->name('cuotas.index');
    Route::post('/cuotas/{cuota}/generar-qr', [PaymentController::class, 'generateInstallmentQR'])->name('cuotas.generateQR');
    Route::get('/cuotas/{cuota}/estado', [PaymentController::class, 'checkInstallmentStatus'])->name('cuotas.status');
    
    // Provider management routes
    Route::resource('proveedores', ProviderController::class);
    
    Route::resource('/materiales', \App\Http\Controllers\MaterialController::class)->names('materiales');
    Route::post('/materiales/{id}/proveedores', [\App\Http\Controllers\MaterialController::class, 'attachProvider'])->name('materiales.attach-provider');
    Route::delete('/materiales/{materialId}/proveedores/{proveedorId}', [\App\Http\Controllers\MaterialController::class, 'detachProvider'])->name('materiales.detach-provider');
    
    // Service management routes
    Route::get('/servicios', [ServicioController::class, 'adminIndex'])->name('servicios.index');
    Route::get('/servicios/crear', [ServicioController::class, 'create'])->name('servicios.create');
    Route::post('/servicios', [ServicioController::class, 'store'])->name('servicios.store');
    Route::get('/servicios/{servicio}/editar', [ServicioController::class, 'edit'])->name('servicios.edit');
    Route::put('/servicios/{servicio}', [ServicioController::class, 'update'])->name('servicios.update');
    Route::delete('/servicios/{servicio}', [ServicioController::class, 'destroy'])->name('servicios.destroy');
    
    // Gastos Operativos routes
    Route::resource('gastos-operativos', \App\Http\Controllers\GastoOperativoController::class)
        ->names('gastos-operativos')
        ->parameters(['gastos-operativos' => 'gastoOperativo']);
    Route::get('/gastos-operativos/reporte/categoria', [\App\Http\Controllers\GastoOperativoController::class, 'reportePorCategoria'])->name('gastos-operativos.reporte-categoria');
    
    // Report routes
    Route::prefix('reportes')->name('reportes.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\ReportController::class, 'dashboard'])->name('dashboard');
        Route::get('/ventas/{year?}', [\App\Http\Controllers\ReportController::class, 'salesByMonth'])->name('ventas');
        Route::get('/rentabilidad', [\App\Http\Controllers\ReportController::class, 'jobsProfitability'])->name('rentabilidad');
        Route::get('/clientes', [\App\Http\Controllers\ReportController::class, 'clientsWithDebt'])->name('clientes');
        Route::get('/inventario', [\App\Http\Controllers\ReportController::class, 'inventoryConsumption'])->name('inventario');
        
        // PDF Exports
        Route::get('/clientes/pdf', [\App\Http\Controllers\ReportController::class, 'exportClientsPdf'])->name('clientes.pdf');
        Route::get('/inventario/pdf', [\App\Http\Controllers\ReportController::class, 'exportInventoryPdf'])->name('inventario.pdf');
        Route::get('/rentabilidad/pdf', [\App\Http\Controllers\ReportController::class, 'exportProfitabilityPdf'])->name('rentabilidad.pdf');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
require __DIR__.'/debug.php';
