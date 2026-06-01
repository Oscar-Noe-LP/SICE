<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'usuario';
    protected $primaryKey = 'id_usuario';

    protected $fillable = [
        'id_persona',
        'nombre_usuario',
        'password_hash',
        'estatus'
    ];

    protected $hidden = ['password_hash'];

    public $timestamps = false;

    // Sanctum usa getAuthPassword() para verificar la contraseña
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'usuario_rol', 'id_usuario', 'id_rol');
    }
}