<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialProveedor extends Model
{
    protected $table = 'material_proveedor';
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'id_material',
        'id_proveedor',
        'precio_unitario',
        'es_principal',
        'tiempo_entrega_dias',
        'notas',
    ];

    protected $casts = [
        'es_principal' => 'boolean',
        'precio_unitario' => 'decimal:2',
    ];

    /**
     * Get the material.
     */
    public function material()
    {
        return $this->belongsTo(Material::class, 'id_material', 'id_material');
    }

    /**
     * Get the provider.
     */
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor', 'id_proveedor');
    }
}
