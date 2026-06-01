<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    protected $table = 'permiso';
    protected $primaryKey = 'id_permiso';
    public $timestamps = false;

    protected $fillable = [
        'nombre_permiso',
        'descripcion'
    ];

    public function modulos()
    {
        return $this->belongsToMany(
            Modulo::class,
            'permiso_modulo',
            'id_permiso',
            'id_modulo'
        );
    }
}