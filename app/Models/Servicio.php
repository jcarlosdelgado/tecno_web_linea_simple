<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Servicio extends Model
{
    protected $table = 'servicios';
    protected $primaryKey = 'id_servicio';

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio_base',
        'imagen',
        'categoria',
        'activo',
        'orden',
    ];

    protected $casts = [
        'precio_base' => 'decimal:2',
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    /**
     * Get the trabajos for this servicio.
     */
    public function trabajos(): HasMany
    {
        return $this->hasMany(Trabajo::class, 'id_servicio', 'id_servicio');
    }

    /**
     * Scope to get only active services.
     */
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope to order by custom order field.
     */
    public function scopeOrdenado($query)
    {
        return $query->orderBy('orden', 'asc')->orderBy('nombre', 'asc');
    }
}
