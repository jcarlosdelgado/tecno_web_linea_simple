<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FotoSeguimiento extends Model
{
    protected $table = 'fotos_seguimiento';
    protected $primaryKey = 'id_foto';
    public $timestamps = false;

    protected $fillable = [
        'id_seguimiento',
        'url_foto',
        'creado_en',
    ];

    protected $casts = [
        'creado_en' => 'datetime',
    ];

    public function seguimiento()
    {
        return $this->belongsTo(SeguimientoTrabajo::class, 'id_seguimiento', 'id_seguimiento');
    }
}
