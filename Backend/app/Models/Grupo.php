<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    protected $table = 'grupo';
    protected $primaryKey = 'id_grupo';
    public $timestamps = false;

    protected $fillable = [
        'id_materia',
        'id_docente',
        'id_periodo',
        'id_aula',
        'clave_grupo',
        'capacidad',
        'estatus',
        'acta_cerrada',
        'fecha_cierre_acta'
    ];
}
