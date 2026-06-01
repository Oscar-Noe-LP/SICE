<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class CatalogosController extends Controller
{
    public function generos()
    {
        try {
            $generos = DB::table('genero')
                ->select('id_genero', 'nombre_genero as nombre')
                ->orderBy('nombre_genero')
                ->get();
            return response()->json($generos);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function estatusAlumno()
    {
        try {
            $estatus = DB::table('estatus_alumno')
                ->select('id_estatus_alumno as id', 'nombre')
                ->orderBy('id_estatus_alumno')
                ->get();
            return response()->json($estatus);
        } catch (\Exception $e) {
            // Si no existe la tabla aún, devolver valores fijos
            return response()->json([
                ['id' => 1, 'nombre' => 'Activo'],
                ['id' => 2, 'nombre' => 'Baja Temporal'],
                ['id' => 3, 'nombre' => 'Baja Definitiva'],
                ['id' => 4, 'nombre' => 'Titulado'],
                ['id' => 5, 'nombre' => 'Egresado'],
            ]);
        }
    }

    public function turnos()
    {
        try {
            $turnos = DB::table('turno')
                ->select('id_turno', 'nombre_turno as nombre')
                ->get();
            return response()->json($turnos);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
