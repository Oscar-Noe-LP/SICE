<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonaCorreo extends Model
{
    use HasFactory;

    protected $table = 'persona_correo';
    protected $primaryKey = 'id_correo';
    public $timestamps = false;

    protected $fillable = [
        'id_persona',
        'correo',
    ];
}