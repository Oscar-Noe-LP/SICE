<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactoEmergenciaAlumno extends Model
{
    protected $table      = 'contacto_emergencia_alumno';
    protected $primaryKey = 'id_contacto';
    public $timestamps = true;

    protected $fillable = [
        'id_alumno',
        'nombre_completo',
        'parentesco',
        'telefono',
        'telefono_alt',
        'es_principal',
    ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'id_alumno', 'id_alumno');
    }
}
