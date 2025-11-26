<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeguimientoTrabajo extends Model
{
    protected $table = 'seguimiento_trabajo';
    protected $primaryKey = 'id_seguimiento';
    const CREATED_AT = 'fecha';
    const UPDATED_AT = null;

    protected $fillable = [
        'id_trabajo',
        'descripcion',
        'porcentaje_avance',
        'horas_trabajadas',
        'evidencias_url', // Kept for backward compatibility or single file
        'fecha',
    ];

    protected $casts = [
        'porcentaje_avance' => 'integer',
        'horas_trabajadas' => 'decimal:2',
        'fecha' => 'datetime',
    ];

    public function trabajo()
    {
        return $this->belongsTo(Trabajo::class, 'id_trabajo', 'id_trabajo');
    }

    public function fotos()
    {
        return $this->hasMany(FotoSeguimiento::class, 'id_seguimiento', 'id_seguimiento');
    }
}
