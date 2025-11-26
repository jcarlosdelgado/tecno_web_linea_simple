<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'id_rol';

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // Relación con permisos
    public function permisos()
    {
        return $this->belongsToMany(
            Permiso::class,
            'roles_permisos',
            'id_rol',
            'id_permiso'
        );
    }

    // Relación con usuarios
    public function usuarios()
    {
        return $this->hasMany(User::class, 'id_rol');
    }

    // Verificar si el rol tiene un permiso específico
    public function tienePermiso($nombrePermiso)
    {
        return $this->permisos()->where('nombre', $nombrePermiso)->exists();
    }
}
