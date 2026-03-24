<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alumno extends Model
{
    protected $table = 'alumno';
    protected $primaryKey = 'id_alumno';

    protected $fillable = [
        'id_persona',
        'numero_control',
        'id_carrera',
        'fecha_ingreso',
        'semestre_actual',
        'estatus'
    ];

    public $timestamps = false;

    // ESTO ES LO QUE FALTA: Relación con Persona
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    // Relación con Carrera
    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'id_carrera', 'id_carrera');
    }
}