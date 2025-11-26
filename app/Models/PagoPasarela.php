<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagoPasarela extends Model
{
    protected $table = 'pagos_pasarela';
    protected $primaryKey = 'id_pasarela';
    const CREATED_AT = 'fecha';
    const UPDATED_AT = null;

    protected $fillable = [
        'id_pago',
        'cuota_id',
        'transaction_id',
        'pf_transaction_id',
        'payment_method_transaction_id',
        'referencia',
        'estado',
        'pf_status',
        'expiration_date',
        'qr_base64',
        'checkout_url',
        'deep_link',
        'qr_content_url',
        'universal_url',
        'respuesta_json',
        'fecha',
    ];

    protected $casts = [
        'expiration_date' => 'datetime',
        'fecha' => 'datetime',
    ];

    public function pago()
    {
        return $this->belongsTo(Pago::class, 'id_pago', 'id_pago');
    }

    public function cuota()
    {
        return $this->belongsTo(CuotaPago::class, 'cuota_id', 'id_cuota');
    }
}
