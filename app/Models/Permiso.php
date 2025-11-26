<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    protected $table = 'permisos';
    protected $primaryKey = 'id_permiso';

    protected $fillable = [
        'nombre',
        'descripcion',
        'modulo',
    ];

    // Relación con roles
    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'roles_permisos',
            'id_permiso',
            'id_rol'
        );
    }
}
