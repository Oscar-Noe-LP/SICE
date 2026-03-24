<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inscripcion extends Model
{
    protected $table = 'inscripcion';
    protected $primaryKey = 'id_inscripcion';
    protected $fillable = ['id_alumno', 'id_grupo', 'fecha_inscripcion', 'estatus'];
    public $timestamps = false;

    // ← Agrega esto
    protected $casts = [
        'fecha_inscripcion' => 'datetime:Y-m-d',   // o 'date' si no necesitas hora
    ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'id_alumno');
    }
}