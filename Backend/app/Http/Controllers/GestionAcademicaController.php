<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GestionAcademicaController extends Controller
{
    public function resumen()
    {
        try {
            $carreras = DB::table('carrera')->count();
            $materias = DB::table('materia')->count();

            // 🔹 QUITAMOS 'activo' para evitar error
            $periodos = DB::table('periodo')->count();

            $aulas = DB::table('aula')->count();

            return response()->json([
                'carreras' => $carreras,
                'materias' => $materias,
                'periodos' => $periodos,
                'aulas'    => $aulas
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener resumen',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}