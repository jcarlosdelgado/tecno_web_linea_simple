<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'usuarios';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id_usuario';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'email',
        'password',
        'telefono',
        'direccion',
        'rol',
        'id_rol',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Role constants
    const ROL_PROPIETARIO = 'PROPIETARIO';
    const ROL_CLIENTE = 'CLIENTE';
    const ROL_EMPLEADO = 'EMPLEADO';

    public function isPropietario(): bool
    {
        return $this->rol === self::ROL_PROPIETARIO;
    }

    public function isCliente(): bool
    {
        return $this->rol === self::ROL_CLIENTE;
    }

    public function isEmpleado(): bool
    {
        return $this->rol === self::ROL_EMPLEADO;
    }

    protected $appends = ['name'];

    public function getNameAttribute()
    {
        return $this->nombre;
    }

    /**
     * Get the trabajos for the user (cliente).
     */
    public function trabajos()
    {
        return $this->hasMany(Trabajo::class, 'id_cliente', 'id_usuario');
    }

    /**
     * Get the trabajos asignados for the user (trabajador).
     */
    public function trabajosAsignados()
    {
        return $this->hasMany(Trabajo::class, 'id_trabajador', 'id_usuario');
    }

    /**
     * Relación con rol personalizado
     */
    public function roleCustom()
    {
        return $this->belongsTo(Role::class, 'id_rol', 'id_rol');
    }

    /**
     * Relación con permisos personalizados del usuario
     */
    public function permisosPersonalizados()
    {
        return $this->belongsToMany(
            Permiso::class,
            'usuarios_permisos',
            'id_usuario',
            'id_permiso'
        );
    }

    /**
     * Verificar si el usuario tiene un permiso específico
     */
    public function tienePermiso($nombrePermiso)
    {
        // Si es propietario, tiene todos los permisos
        if ($this->isPropietario()) {
            return true;
        }

        // Primero verificar permisos personalizados
        if ($this->permisosPersonalizados()->where('nombre', $nombrePermiso)->exists()) {
            return true;
        }

        // Si no tiene permisos personalizados, verificar permisos del rol
        if ($this->roleCustom && $this->permisosPersonalizados()->count() === 0) {
            return $this->roleCustom->tienePermiso($nombrePermiso);
        }

        return false;
    }

    /**
     * Obtener todos los permisos del usuario
     */
    public function getPermisosAttribute()
    {
        if ($this->isPropietario()) {
            return Permiso::all();
        }

        // Si tiene permisos personalizados, usar esos
        if ($this->permisosPersonalizados()->count() > 0) {
            return $this->permisosPersonalizados;
        }

        // Si no, usar los del rol
        if ($this->roleCustom) {
            return $this->roleCustom->permisos;
        }

        return collect();
    }
}
