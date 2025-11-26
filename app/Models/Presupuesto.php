<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Presupuesto extends Model
{
    protected $table = 'presupuestos';
    protected $primaryKey = 'id_presupuesto';
    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = null;

    protected $fillable = [
        'id_trabajo',
        'monto_total',
        'mano_obra',
        'otros_costos',
        'estado',
        'fecha_emision',
        'fecha_validez',
        'notas',
        'notas_adicionales',
    ];

    protected $casts = [
        'monto_total' => 'decimal:2',
        'mano_obra' => 'decimal:2',
        'otros_costos' => 'decimal:2',
        'fecha_emision' => 'date',
        'fecha_validez' => 'date',
    ];

    public function trabajo()
    {
        return $this->belongsTo(Trabajo::class, 'id_trabajo', 'id_trabajo');
    }

    public function detalles()
    {
        return $this->hasMany(PresupuestoDetalleMaterial::class, 'id_presupuesto', 'id_presupuesto');
    }
}
