<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Especialidad extends Model
{
    protected $table      = 'especialidad';
    protected $primaryKey = 'id_especialidad';
    // La tabla sí tiene created_at / updated_at
    public $timestamps = true;

    protected $fillable = [
        'id_carrera',
        'nombre',
        'descripcion',
        'estatus',
    ];

    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'id_carrera', 'id_carrera');
    }

    public function alumnos()
    {
        return $this->hasMany(Alumno::class, 'id_especialidad', 'id_especialidad');
    }
}
