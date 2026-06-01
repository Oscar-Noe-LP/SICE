<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class CarreraResumenController extends Controller
{
    public function index()
    {
        // Obtener periodo activo
        $periodoActual = DB::table('periodo')
            ->where('estatus', 1)
            ->first();

        if (!$periodoActual) {
            return response()->json([]);
        }

        // Obtener carreras activas
        $carreras = DB::table('carrera')
            ->select(
                'id_carrera',
                'nombre'
            )
            ->where('estatus', 1)
            ->get();

        $resultado = [];

        foreach ($carreras as $carrera) {

            // Total alumnos activos
            $totalAlumnos = DB::table('alumno')
                ->where('id_carrera', $carrera->id_carrera)
                ->where('estatus', 'Activo')
                ->count();

            // Total grupos del periodo actual
            $totalGrupos = DB::table('grupo')
                ->join('inscripcion', 'grupo.id_grupo', '=', 'inscripcion.id_grupo')
                ->join('alumno', 'inscripcion.id_alumno', '=', 'alumno.id_alumno')
                ->where('alumno.id_carrera', $carrera->id_carrera)
                ->where('grupo.estatus', 1)
                ->where('grupo.id_periodo', $periodoActual->id_periodo)
                ->distinct('grupo.id_grupo')
                ->count('grupo.id_grupo');

            // Alumnos irregulares
            $alumnosIrregulares = DB::table('alumno')
                ->join('inscripcion', 'alumno.id_alumno', '=', 'inscripcion.id_alumno')
                ->join('grupo', 'inscripcion.id_grupo', '=', 'grupo.id_grupo')
                ->join('calificacion', 'inscripcion.id_inscripcion', '=', 'calificacion.id_inscripcion')
                ->where('alumno.id_carrera', $carrera->id_carrera)
                ->where('grupo.id_periodo', $periodoActual->id_periodo)
                ->where('calificacion.calificacion', '<', 70)
                ->distinct('alumno.id_alumno')
                ->count('alumno.id_alumno');

            // Alumnos regulares
            $alumnosRegulares = $totalAlumnos - $alumnosIrregulares;

            $resultado[] = [
                'id_carrera' => $carrera->id_carrera,
                'nombre' => $carrera->nombre,
                'total_grupos' => $totalGrupos,
                'total_alumnos' => $totalAlumnos,
                'alumnos_regulares' => $alumnosRegulares,
                'alumnos_irregulares' => $alumnosIrregulares,
            ];
        }

        return response()->json($resultado);
    }
}
