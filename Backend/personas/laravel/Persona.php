<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Persona
 *
 * Representa la tabla persona en la base de datos.
 * Es la entidad maestra del sistema de la que dependen
 * alumnos, empleados y usuarios.
 */
class Persona extends Model
{
    // Nombre de la tabla en la base de datos
    protected $table = 'persona';

    // Llave primaria de la tabla
    protected $primaryKey = 'id_persona';

    // Se desactivan los timestamps automáticos porque
    // la tabla usa fecha_creacion con valor por defecto en la BD
    public $timestamps = false;

    // Campos permitidos para asignación masiva
    protected $fillable = [
        'nombre',           // Nombre(s) de la persona
        'apellido_paterno', // Apellido paterno
        'apellido_materno', // Apellido materno (opcional)
        'curp',             // CURP único de 18 caracteres
        'fecha_nacimiento', // Fecha de nacimiento
        'id_genero',        // FK hacia catálogo genero
        'estatus',          // 1 = activo, 0 = inactivo
    ];
}