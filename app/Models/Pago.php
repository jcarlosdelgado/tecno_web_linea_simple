<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $table = 'pagos';
    protected $primaryKey = 'id_pago';
    const CREATED_AT = 'fecha';
    const UPDATED_AT = null;

    protected $fillable = [
        'id_trabajo',
        'monto',
        'metodo', // Deprecated but kept for compatibility
        'tipo_pago',
        'numero_cuotas',
        'monto_cuota',
        'metodo_pago',
        'estado',
        'comprobante_url',
        'transaction_id',
        'fecha'
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'monto_cuota' => 'decimal:2',
        'fecha' => 'datetime',
    ];

    public function trabajo()
    {
        return $this->belongsTo(Trabajo::class, 'id_trabajo', 'id_trabajo');
    }

    public function cuotas()
    {
        return $this->hasMany(CuotaPago::class, 'id_pago', 'id_pago');
    }

    public function pasarela()
    {
        return $this->hasOne(PagoPasarela::class, 'id_pago', 'id_pago')
            ->whereNull('cuota_id'); // Solo el registro del pago completo, no de cuotas
    }
}
