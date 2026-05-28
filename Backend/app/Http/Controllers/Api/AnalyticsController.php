<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * GET /api/analytics/tronco-comun?periodo_id=3
     *
     * Devuelve resumen de aprobación/reprobación por periodo.
     * Umbral de aprobación: calificación >= 70 (consistente con KardexController).
     *
     * Respuesta:
     * {
     *   "periodo": "2026-1",
     *   "total_alumnos": 312,
     *   "aprobados": 240,
     *   "reprobados": 72,
     *   "porcentaje_aprobacion": 76.9,
     *   "por_materia": [
     *     { "materia": "Fundamentos de Programacion", "aprobados": 28, "reprobados": 7, "porcentaje": 80.0 }
     *   ]
     * }
     */
    public function troncoComun(Request $request)
    {
        $periodoId = $request->query('periodo_id');

        if (!$periodoId) {
            return response()->json(['error' => 'El parámetro periodo_id es requerido.'], 422);
        }

        // Validar que el periodo exista
        $periodo = DB::table('periodo')->where('id_periodo', $periodoId)->first();

        if (!$periodo) {
            return response()->json(['error' => 'Periodo no encontrado.'], 404);
        }

        try {
            /*
             * Calificación por inscripción: se toma el promedio ponderado
             * de todas las evaluaciones del grupo (calificacion * porcentaje / 100).
             * Si un alumno no tiene calificaciones registradas, se cuenta como reprobado.
             */
            $resultados = DB::table('inscripcion as i')
                ->join('grupo as g', 'i.id_grupo', '=', 'g.id_grupo')
                ->join('materia as m', 'g.id_materia', '=', 'm.id_materia')
                ->where('g.id_periodo', $periodoId)
                ->leftJoin(
                    DB::raw('(
                        SELECT
                            c.id_inscripcion,
                            SUM(c.calificacion * e.porcentaje / 100) as calificacion_ponderada
                        FROM calificacion c
                        INNER JOIN evaluacion e ON c.id_evaluacion = e.id_evaluacion
                        GROUP BY c.id_inscripcion
                    ) as cal_pond'),
                    'cal_pond.id_inscripcion', '=', 'i.id_inscripcion'
                )
                ->select(
                    'm.id_materia',
                    'm.nombre as nombre_materia',
                    'i.id_alumno',
                    DB::raw('COALESCE(cal_pond.calificacion_ponderada, 0) as calificacion_final')
                )
                ->get();

            if ($resultados->isEmpty()) {
                return response()->json([
                    'periodo'               => $periodo->nombre_periodo,
                    'total_alumnos'         => 0,
                    'aprobados'             => 0,
                    'reprobados'            => 0,
                    'porcentaje_aprobacion' => 0.0,
                    'por_materia'           => [],
                ]);
            }

            // Alumnos únicos en el periodo
            $totalAlumnos = $resultados->pluck('id_alumno')->unique()->count();

            // Conteo global (un alumno puede tener múltiples inscripciones,
            // contamos aprobado/reprobado por inscripción, no por alumno)
            $aprobadosTotal  = $resultados->where('calificacion_final', '>=', 70)->count();
            $reprobadosTotal = $resultados->where('calificacion_final', '<', 70)->count();
            $totalRegistros  = $resultados->count();

            $porcentajeAprobacion = $totalRegistros > 0
                ? round(($aprobadosTotal / $totalRegistros) * 100, 1)
                : 0.0;

            // Desglose por materia
            $porMateria = $resultados
                ->groupBy('id_materia')
                ->map(function ($filas) {
                    $aprobados  = $filas->where('calificacion_final', '>=', 70)->count();
                    $reprobados = $filas->where('calificacion_final', '<', 70)->count();
                    $total      = $filas->count();

                    return [
                        'materia'    => $filas->first()->nombre_materia,
                        'aprobados'  => $aprobados,
                        'reprobados' => $reprobados,
                        'porcentaje' => $total > 0 ? round(($aprobados / $total) * 100, 1) : 0.0,
                    ];
                })
                ->values();

            return response()->json([
                'periodo'               => $periodo->nombre_periodo,
                'total_alumnos'         => $totalAlumnos,
                'aprobados'             => $aprobadosTotal,
                'reprobados'            => $reprobadosTotal,
                'porcentaje_aprobacion' => $porcentajeAprobacion,
                'por_materia'           => $porMateria,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}