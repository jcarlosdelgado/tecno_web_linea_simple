<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresupuestoDetalleMaterial extends Model
{
    protected $table = 'presupuesto_detalle_material';
    protected $primaryKey = 'id_detalle';
    public $timestamps = false;

    protected $fillable = [
        'id_presupuesto',
        'id_material',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class, 'id_material', 'id_material');
    }

    public function presupuesto()
    {
        return $this->belongsTo(Presupuesto::class, 'id_presupuesto', 'id_presupuesto');
    }
}
