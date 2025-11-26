<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimientoMaterial extends Model
{
    protected $table = 'movimientos_material';
    protected $primaryKey = 'id_movimiento';
    const CREATED_AT = 'fecha';
    const UPDATED_AT = null;

    protected $fillable = [
        'id_material',
        'tipo_movimiento',
        'cantidad',
        'precio_unitario',
        'id_proveedor',
        'id_trabajo',
        'fecha',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class, 'id_material');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor');
    }
}
