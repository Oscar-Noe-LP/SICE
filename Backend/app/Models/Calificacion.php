<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Calificacion extends Model
{
    protected $table = 'calificacion';
    protected $primaryKey = 'id_calificacion';
    public $timestamps = false;
    protected $fillable = [
        'id_inscripcion',
        'id_evaluacion',
        'calificacion',
        'fecha_registro',
    ];
}