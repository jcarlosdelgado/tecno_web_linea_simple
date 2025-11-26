<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Presupuesto;

class Trabajo extends Model
{
    protected $table = 'trabajos';
    protected $primaryKey = 'id_trabajo';
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'id_cliente',
        'id_servicio',
        'id_trabajador',
        'titulo',
        'descripcion',
        'imagenes_referencia',
        'medidas',
        'cantidad',
        'colores',
        'fecha_estimada',
        'estado',
        'fecha_solicitud',
        'fecha_inicio',
        'fecha_fin',
    ];

    protected $casts = [
        'imagenes_referencia' => 'array',
        'medidas' => 'array',
        'fecha_solicitud' => 'datetime',
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'fecha_estimada' => 'date',
    ];

    /**
     * Get the cliente that owns the trabajo.
     */
    public function cliente()
    {
        return $this->belongsTo(User::class, 'id_cliente', 'id_usuario');
    }

    /**
     * Get the servicio that this trabajo is based on.
     */
    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'id_servicio', 'id_servicio');
    }

    /**
     * Get the trabajador assigned to this trabajo.
     */
    public function trabajador()
    {
        return $this->belongsTo(User::class, 'id_trabajador', 'id_usuario');
    }

    public function presupuestos()
    {
        return $this->hasMany(Presupuesto::class, 'id_trabajo', 'id_trabajo');
    }

    /**
     * Get the pagos for this trabajo.
     */
    public function pagos()
    {
        return $this->hasMany(\App\Models\Pago::class, 'id_trabajo', 'id_trabajo');
    }

    /**
     * Get the seguimientos for this trabajo.
     */
    public function seguimientos()
    {
        return $this->hasMany(\App\Models\SeguimientoTrabajo::class, 'id_trabajo', 'id_trabajo');
    }
}
