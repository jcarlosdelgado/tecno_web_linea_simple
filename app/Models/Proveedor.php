<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    protected $table = 'proveedores';
    protected $primaryKey = 'id_proveedor';
    public $timestamps = true;
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null;

    protected $fillable = [
        'nombre',
        'telefono',
        'email',
        'direccion',
        'contacto',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Get the materials supplied by this provider.
     */
    public function materiales()
    {
        return $this->belongsToMany(
            Material::class,
            'material_proveedor',
            'id_proveedor',
            'id_material'
        )->withPivot('precio_unitario', 'es_principal', 'tiempo_entrega_dias', 'notas')
          ->withTimestamps('creado_en', 'actualizado_en');
    }
}
