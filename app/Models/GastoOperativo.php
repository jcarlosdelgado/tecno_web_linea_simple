<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GastoOperativo extends Model
{
    protected $table = 'gastos_operativos';
    protected $primaryKey = 'id_gasto_operativo';
    public $incrementing = true;

    protected $fillable = [
        'categoria',
        'descripcion',
        'monto',
        'fecha',
        'comprobante',
        'registrado_por',
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'id_gasto_operativo';
    }

    /**
     * Relación con el usuario que registró el gasto
     */
    public function registrador()
    {
        return $this->belongsTo(User::class, 'registrado_por', 'id_usuario');
    }
}
