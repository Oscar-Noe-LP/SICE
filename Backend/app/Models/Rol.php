<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $table = 'rol';
    protected $primaryKey = 'id_rol';

    protected $fillable = [
        'nombre_rol',
        'descripcion',
        'estatus'
    ];

    public $timestamps = false;

    // Relación con permisos (muchos a muchos)
    public function permisos()
    {
        return $this->belongsToMany(
            \App\Models\Permiso::class,
            'rol_permiso',
            'id_rol',
            'id_permiso'
        )->select('permiso.id_permiso', 'permiso.nombre_permiso');
    }
}