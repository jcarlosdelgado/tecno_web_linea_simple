<?php

namespace App\Http\Controllers;

use App\Models\Trabajo;
use App\Models\Presupuesto;
use App\Models\Pago;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    /**
     * Generate quotation PDF
     */
    public function downloadQuotation($id)
    {
        $presupuesto = Presupuesto::with([
            'trabajo.cliente',
            'trabajo.servicio',
            'detalles.material'
        ])->findOrFail($id);

        // Verify user can access this quotation
        if (auth()->user()->rol === 'CLIENTE') {
            if ($presupuesto->trabajo->id_cliente !== auth()->id()) {
                abort(403, 'No autorizado para ver esta cotización');
            }
        }

        $pdf = Pdf::loadView('pdf.quotation', [
            'presupuesto' => $presupuesto,
            'trabajo' => $presupuesto->trabajo,
            'cliente' => $presupuesto->trabajo->cliente,
        ]);

        return $pdf->download('cotizacion-' . $presupuesto->id_presupuesto . '.pdf');
    }

    /**
     * Generate invoice/receipt PDF
     */
    public function downloadInvoice($id)
    {
        $pago = Pago::with([
            'trabajo.cliente',
            'trabajo.servicio',
            'trabajo.presupuestos' => function($query) {
                $query->where('estado', 'APROBADO');
            },
            'cuotas'
        ])->findOrFail($id);

        // Verify user can access this invoice
        if (auth()->user()->rol === 'CLIENTE') {
            if ($pago->trabajo->id_cliente !== auth()->id()) {
                abort(403, 'No autorizado para ver este comprobante');
            }
        }

        // Only generate invoice for completed payments
        if ($pago->estado !== 'COMPLETADO') {
            return back()->withErrors(['error' => 'El pago aún no está completado']);
        }

        $pdf = Pdf::loadView('pdf.invoice', [
            'pago' => $pago,
            'trabajo' => $pago->trabajo,
            'cliente' => $pago->trabajo->cliente,
            'presupuesto' => $pago->trabajo->presupuestos->first(),
        ]);

        return $pdf->download('comprobante-' . $pago->id_pago . '.pdf');
    }

    /**
     * View quotation PDF in browser
     */
    public function viewQuotation($id)
    {
        $presupuesto = Presupuesto::with([
            'trabajo.cliente',
            'trabajo.servicio',
            'detalles.material'
        ])->findOrFail($id);

        // Verify user can access this quotation
        if (auth()->user()->rol === 'CLIENTE') {
            if ($presupuesto->trabajo->id_cliente !== auth()->id()) {
                abort(403, 'No autorizado para ver esta cotización');
            }
        }

        $pdf = Pdf::loadView('pdf.quotation', [
            'presupuesto' => $presupuesto,
            'trabajo' => $presupuesto->trabajo,
            'cliente' => $presupuesto->trabajo->cliente,
        ]);

        return $pdf->stream('cotizacion-' . $presupuesto->id_presupuesto . '.pdf');
    }

    /**
     * View invoice PDF in browser
     */
    public function viewInvoice($id)
    {
        $pago = Pago::with([
            'trabajo.cliente',
            'trabajo.servicio',
            'trabajo.presupuestos' => function($query) {
                $query->where('estado', 'APROBADO');
            },
            'cuotas'
        ])->findOrFail($id);

        // Verify user can access this invoice
        if (auth()->user()->rol === 'CLIENTE') {
            if ($pago->trabajo->id_cliente !== auth()->id()) {
                abort(403, 'No autorizado para ver este comprobante');
            }
        }

        if ($pago->estado !== 'COMPLETADO') {
            return back()->withErrors(['error' => 'El pago aún no está completado']);
        }

        $pdf = Pdf::loadView('pdf.invoice', [
            'pago' => $pago,
            'trabajo' => $pago->trabajo,
            'cliente' => $pago->trabajo->cliente,
            'presupuesto' => $pago->trabajo->presupuestos->first(),
        ]);

        return $pdf->stream('comprobante-' . $pago->id_pago . '.pdf');
    }
}
