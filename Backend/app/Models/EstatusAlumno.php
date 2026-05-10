<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstatusAlumno extends Model
{
    protected $table      = 'estatus_alumno';
    protected $primaryKey = 'id_estatus_alumno';
    public    $timestamps = false;

    protected $fillable = ['nombre'];
}
