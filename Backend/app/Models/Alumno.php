<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alumno extends Model
{
    protected $table      = 'alumno';
    protected $primaryKey = 'id_alumno';
    public    $timestamps = false;

    protected $fillable = [
        'id_persona',
        'numero_control',
        'id_carrera',
        'fecha_ingreso',
        'semestre_actual',
        'estatus',
        'id_estatus_alumno',

        // ── Campos agregados por migracion_alumno_campos_nuevos.sql ───────
        'id_especialidad',  // FK → tabla especialidad
        'nss',              // Número de Seguridad Social (varchar 11)
        'folio_subes',      // Folio SUBES / Beca Benito Juárez
        'tipo_sangre',      // ENUM: A+, A-, B+, B-, AB+, AB-, O+, O-
        'discapacidad',     // Descripción breve, NULL = ninguna
    ];

    // ── Relaciones ──────────────────────────────────────────────────────

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'id_carrera', 'id_carrera');
    }

    public function estatusAlumno()
    {
        return $this->belongsTo(\App\Models\EstatusAlumno::class, 'id_estatus_alumno', 'id_estatus_alumno');
    }

    /**
     * Especialidad del alumno dentro de su carrera.
     * Tabla: especialidad (id_especialidad, id_carrera, nombre)
     */
    public function especialidad()
    {
        return $this->belongsTo(\App\Models\Especialidad::class, 'id_especialidad', 'id_especialidad');
    }

    /**
     * Contactos de emergencia. Tabla: contacto_emergencia_alumno.
     * El principal tiene es_principal = 1.
     */
    public function contactosEmergencia()
    {
        return $this->hasMany(\App\Models\ContactoEmergenciaAlumno::class, 'id_alumno', 'id_alumno');
    }

    public function contactoPrincipal()
    {
        return $this->hasOne(\App\Models\ContactoEmergenciaAlumno::class, 'id_alumno', 'id_alumno')
                    ->where('es_principal', 1);
    }
}
