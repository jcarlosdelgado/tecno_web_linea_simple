<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $table = 'materiales';
    protected $primaryKey = 'id_material';
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'nombre',
        'descripcion',
        'unidad_medida',
        'stock_actual',
        'stock_minimo',
        'precio_unitario',
    ];

    /**
     * Get the providers that supply this material.
     */
    public function proveedores()
    {
        return $this->belongsToMany(
            Proveedor::class,
            'material_proveedor',
            'id_material',
            'id_proveedor'
        )->withPivot('precio_unitario', 'es_principal', 'tiempo_entrega_dias', 'notas')
          ->withTimestamps('creado_en', 'actualizado_en');
    }

    /**
     * Get the main provider for this material.
     */
    public function proveedorPrincipal()
    {
        return $this->proveedores()->wherePivot('es_principal', true)->first();
    }
}

