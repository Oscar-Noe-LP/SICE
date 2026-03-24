<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    protected $table = 'persona';
    protected $primaryKey = 'id_persona';
    public $timestamps = false;

    protected $fillable = [
        'nombre', 
        'apellido_paterno', 
        'apellido_materno', 
        'curp', 
        'fecha_nacimiento', 
        'id_genero'
    ];
}