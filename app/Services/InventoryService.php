<?php

namespace App\Services;

use App\Models\Material;
use App\Models\Presupuesto;
use App\Models\PresupuestoDetalleMaterial;
use App\Models\MovimientoMaterial;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Check if there's enough stock for a material.
     */
    public function checkStock(int $materialId, float $cantidad): bool
    {
        $material = Material::find($materialId);
        
        if (!$material) {
            return false;
        }
        
        return $material->cantidad_disponible >= $cantidad;
    }

    /**
     * Check stock for multiple materials.
     * Returns array of materials with insufficient stock.
     */
    public function checkMultipleStock(array $materiales): array
    {
        $insufficient = [];
        
        foreach ($materiales as $item) {
            $materialId = $item['id_material'];
            $cantidad = $item['cantidad'];
            
            if (!$this->checkStock($materialId, $cantidad)) {
                $material = Material::find($materialId);
                $insufficient[] = [
                    'id_material' => $materialId,
                    'nombre' => $material->nombre,
                    'requerido' => $cantidad,
                    'disponible' => $material->cantidad_disponible,
                ];
            }
        }
        
        return $insufficient;
    }

    /**
     * Consume materials from inventory when budget is approved.
     */
    public function consumeMaterials(int $presupuestoId): void
    {
        $presupuesto = Presupuesto::with('detalles')->findOrFail($presupuestoId);
        
        DB::transaction(function () use ($presupuesto) {
            foreach ($presupuesto->detalles as $detalle) {
                $material = Material::findOrFail($detalle->id_material);
                
                // Decrease stock
                $material->cantidad_disponible -= $detalle->cantidad;
                $material->save();
                
                // Register movement
                MovimientoMaterial::create([
                    'id_material' => $material->id_material,
                    'tipo_movimiento' => 'SALIDA',
                    'cantidad' => $detalle->cantidad,
                    'fecha_movimiento' => now(),
                    'motivo' => "Consumo para presupuesto #{$presupuesto->id_presupuesto} - Trabajo #{$presupuesto->id_trabajo}",
                ]);
            }
        });
    }

    /**
     * Consume materials for a specific job.
     * Used when approving a budget.
     */
    public function consumeMaterialsForJob($trabajo, array $materiales): void
    {
        DB::transaction(function () use ($trabajo, $materiales) {
            foreach ($materiales as $item) {
                $material = Material::findOrFail($item['id_material']);
                
                // Check if there's enough stock
                if ($material->cantidad_disponible < $item['cantidad']) {
                    throw new \Exception("Stock insuficiente para {$material->nombre}");
                }
                
                // Decrease stock
                $material->cantidad_disponible -= $item['cantidad'];
                $material->save();
                
                // Register movement
                MovimientoMaterial::create([
                    'id_material' => $material->id_material,
                    'tipo_movimiento' => 'SALIDA',
                    'cantidad' => $item['cantidad'],
                    'fecha_movimiento' => now(),
                    'motivo' => "Consumo para trabajo #{$trabajo->id_trabajo} - {$trabajo->titulo}",
                ]);
            }
        });
    }

    /**
     * Get materials with low stock (less than minimum).
     */
    public function getLowStockMaterials(int $threshold = 10): array
    {
        return Material::where('cantidad_disponible', '<', $threshold)
            ->orderBy('cantidad_disponible', 'asc')
            ->get()
            ->map(function ($material) {
                return [
                    'id_material' => $material->id_material,
                    'nombre' => $material->nombre,
                    'cantidad_disponible' => $material->cantidad_disponible,
                    'unidad_medida' => $material->unidad_medida,
                    'precio_unitario' => $material->precio_unitario,
                ];
            })
            ->toArray();
    }

    /**
     * Calculate total cost of materials for a budget.
     */
    public function calculateMaterialsCost(array $materiales): float
    {
        $total = 0;
        
        foreach ($materiales as $item) {
            $material = Material::find($item['id_material']);
            if ($material) {
                $total += $material->precio_unitario * $item['cantidad'];
            }
        }
        
        return $total;
    }

    /**
     * Reserve materials (mark as reserved, not consumed yet).
     * This is optional - can be used to prevent double-booking.
     */
    public function reserveMaterials(int $presupuestoId): void
    {
        // This could be implemented if you want to add a "cantidad_reservada" field
        // to the materials table to prevent overbooking
        // For now, we'll skip this and just consume on approval
    }
}
