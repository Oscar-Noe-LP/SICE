<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bitacora extends Model
{
    protected $table = 'bitacora';
    protected $primaryKey = 'id_bitacora';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'id_modulo',
        'accion',
        'fecha_hora',
        'direccion_ip',
        'datos_anteriores',
        'datos_nuevos'
    ];

    // Relaciones (opcional pero útil)
    public function usuario()
    {
        return $this->belongsTo(\App\Models\Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function modulo()
    {
        return $this->belongsTo(\App\Models\Modulo::class, 'id_modulo', 'id_modulo');
    }
}