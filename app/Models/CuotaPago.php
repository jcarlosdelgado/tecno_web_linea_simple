<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CuotaPago extends Model
{
    use HasFactory;

    protected $table = 'cuotas_pago';
    protected $primaryKey = 'id_cuota';

    protected $fillable = [
        'id_pago',
        'numero_cuota',
        'monto',
        'fecha_vencimiento',
        'fecha_pago',
        'estado',
        'comprobante_url'
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
        'fecha_pago' => 'date',
        'monto' => 'decimal:2',
    ];

    public function pago()
    {
        return $this->belongsTo(Pago::class, 'id_pago', 'id_pago');
    }

    public function pagoPasarela()
    {
        return $this->hasOne(PagoPasarela::class, 'cuota_id', 'id_cuota');
    }
}
