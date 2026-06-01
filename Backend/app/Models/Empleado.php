<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    protected $table = 'empleado';
    protected $primaryKey = 'id_empleado';
    public $timestamps = false;

    protected $fillable = [
        'id_persona',
        'numero_empleado',
        'id_puesto',
        'fecha_contratacion',
        'estatus',
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    public function docente()
    {
        return $this->hasOne(Docente::class, 'id_empleado', 'id_empleado');
    }
}
