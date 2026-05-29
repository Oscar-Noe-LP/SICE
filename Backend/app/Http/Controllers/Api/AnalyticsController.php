<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // Sub-query reutilizable: calificación ponderada por inscripción
    // ─────────────────────────────────────────────────────────────────────────
    private function subqueryCalPonderada(): string
    {
        return '(
            SELECT
                c.id_inscripcion,
                SUM(c.calificacion * e.porcentaje / 100) as calificacion_ponderada
            FROM calificacion c
            INNER JOIN evaluacion e ON c.id_evaluacion = e.id_evaluacion
            GROUP BY c.id_inscripcion
        ) as cal_pond';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // D1 — GET /api/analytics/aprobados-reprobados
    //      ?carrera_id=1  &periodo_id=3
    // ─────────────────────────────────────────────────────────────────────────
    public function aprobadosReprobados(Request $request)
    {
        $carreraId = $request->query('carrera_id');
        $periodoId = $request->query('periodo_id');

        // Validar registros opcionales
        $periodo = $periodoId
            ? DB::table('periodo')->where('id_periodo', $periodoId)->first()
            : null;

        if ($periodoId && !$periodo) {
            return response()->json(['error' => 'Periodo no encontrado.'], 404);
        }

        $carrera = $carreraId
            ? DB::table('carrera')->where('id_carrera', $carreraId)->first()
            : null;

        if ($carreraId && !$carrera) {
            return response()->json(['error' => 'Carrera no encontrada.'], 404);
        }

        try {
            $query = DB::table('inscripcion as i')
                ->join('grupo as g',   'i.id_grupo',   '=', 'g.id_grupo')
                ->join('materia as m', 'g.id_materia', '=', 'm.id_materia');

            if ($periodoId) {
                $query->where('g.id_periodo', $periodoId);
            }

            // Filtro por carrera a través de plan_materia → plan_estudio
            if ($carreraId) {
                $query->join('plan_materia as pm', 'm.id_materia', '=', 'pm.id_materia')
                      ->join('plan_estudio as pe',  'pm.id_plan',   '=', 'pe.id_plan')
                      ->where('pe.id_carrera', $carreraId);
            }

            $resultados = $query
                ->leftJoin(DB::raw($this->subqueryCalPonderada()),
                    'cal_pond.id_inscripcion', '=', 'i.id_inscripcion')
                ->select(
                    'm.id_materia',
                    'm.nombre as nombre_materia',
                    'i.id_alumno',
                    DB::raw('COALESCE(cal_pond.calificacion_ponderada, 0) as calificacion_final')
                )
                ->get();

            $vacio = [
                'carrera'               => $carrera ? $carrera->nombre : 'Todas',
                'periodo'               => $periodo ? $periodo->nombre_periodo : 'Todos',
                'total_alumnos'         => 0,
                'aprobados'             => 0,
                'reprobados'            => 0,
                'porcentaje_aprobacion' => 0.0,
                'por_materia'           => [],
            ];

            if ($resultados->isEmpty()) {
                return response()->json($vacio);
            }

            $totalAlumnos    = $resultados->pluck('id_alumno')->unique()->count();
            $aprobadosTotal  = $resultados->where('calificacion_final', '>=', 70)->count();
            $reprobadosTotal = $resultados->where('calificacion_final', '<',  70)->count();
            $totalRegistros  = $resultados->count();

            $porcentajeAprobacion = $totalRegistros > 0
                ? round(($aprobadosTotal / $totalRegistros) * 100, 1)
                : 0.0;

            $porMateria = $resultados
                ->groupBy('id_materia')
                ->map(function ($filas) {
                    $aprobados  = $filas->where('calificacion_final', '>=', 70)->count();
                    $reprobados = $filas->where('calificacion_final', '<',  70)->count();
                    $total      = $filas->count();

                    return [
                        'materia'    => $filas->first()->nombre_materia,
                        'aprobados'  => $aprobados,
                        'reprobados' => $reprobados,
                        'porcentaje' => $total > 0 ? round(($aprobados / $total) * 100, 1) : 0.0,
                    ];
                })
                ->sortByDesc('reprobados')
                ->values();

            return response()->json([
                'carrera'               => $carrera ? $carrera->nombre : 'Todas',
                'periodo'               => $periodo ? $periodo->nombre_periodo : 'Todos',
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

    // ─────────────────────────────────────────────────────────────────────────
    // D2 — GET /api/analytics/tronco-comun
    //      ?periodo_id=3
    //
    // Tronco común = materias de semestre 1 y 2 en plan_materia.
    // "en_riesgo" si porcentaje_aprobacion < 70 %.
    // ─────────────────────────────────────────────────────────────────────────
    public function troncoComun(Request $request)
    {
        $periodoId = $request->query('periodo_id');

        if (!$periodoId) {
            return response()->json(['error' => 'El parámetro periodo_id es requerido.'], 422);
        }

        $periodo = DB::table('periodo')->where('id_periodo', $periodoId)->first();
        if (!$periodo) {
            return response()->json(['error' => 'Periodo no encontrado.'], 404);
        }

        try {
            $resultados = DB::table('inscripcion as i')
                ->join('grupo as g',       'i.id_grupo',   '=', 'g.id_grupo')
                ->join('materia as m',     'g.id_materia', '=', 'm.id_materia')
                ->join('plan_materia as pm','m.id_materia', '=', 'pm.id_materia')
                ->where('g.id_periodo', $periodoId)
                ->whereIn('pm.semestre', [1, 2])      // tronco común: primeros 2 semestres
                ->leftJoin(DB::raw($this->subqueryCalPonderada()),
                    'cal_pond.id_inscripcion', '=', 'i.id_inscripcion')
                ->select(
                    'm.id_materia',
                    'm.nombre as nombre_materia',
                    'pm.semestre',
                    DB::raw('COALESCE(cal_pond.calificacion_ponderada, 0) as calificacion_final')
                )
                ->get();

            if ($resultados->isEmpty()) {
                return response()->json([]);
            }

            $porMateria = $resultados
                ->groupBy('id_materia')
                ->map(function ($filas) {
                    $total      = $filas->count();
                    $aprobados  = $filas->where('calificacion_final', '>=', 70)->count();
                    $reprobados = $total - $aprobados;
                    $porcentaje = $total > 0 ? round(($aprobados / $total) * 100, 1) : 0.0;
                    $promedio   = $total > 0 ? round($filas->avg('calificacion_final'), 1) : 0.0;

                    return [
                        'materia'               => $filas->first()->nombre_materia,
                        'semestre'              => $filas->first()->semestre,
                        'es_tronco_comun'       => true,
                        'promedio_general'      => $promedio,
                        'porcentaje_aprobacion' => $porcentaje,
                        'aprobados'             => $aprobados,
                        'reprobados'            => $reprobados,
                        'en_riesgo'             => $porcentaje < 70.0,
                    ];
                })
                ->sortBy('semestre')
                ->values();

            return response()->json($porMateria);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // D3 — GET /api/analytics/materias-riesgo
    //      ?carrera_id=1  &limite=10 (default)
    //
    // Top materias con mayor índice de reprobación.
    // ─────────────────────────────────────────────────────────────────────────
    public function materiasRiesgo(Request $request)
    {
        $carreraId = $request->query('carrera_id');
        $limite    = max(1, (int) $request->query('limite', 10));

        try {
            $query = DB::table('inscripcion as i')
                ->join('grupo as g',        'i.id_grupo',   '=', 'g.id_grupo')
                ->join('materia as m',      'g.id_materia', '=', 'm.id_materia')
                ->join('plan_materia as pm','m.id_materia', '=', 'pm.id_materia');

            if ($carreraId) {
                $query->join('plan_estudio as pe', 'pm.id_plan', '=', 'pe.id_plan')
                      ->where('pe.id_carrera', $carreraId);
            }

            $resultados = $query
                ->leftJoin(DB::raw($this->subqueryCalPonderada()),
                    'cal_pond.id_inscripcion', '=', 'i.id_inscripcion')
                ->select(
                    'm.id_materia',
                    'm.nombre as nombre_materia',
                    'pm.semestre',
                    DB::raw('COALESCE(cal_pond.calificacion_ponderada, 0) as calificacion_final')
                )
                ->get();

            if ($resultados->isEmpty()) {
                return response()->json([]);
            }

            $porMateria = $resultados
                ->groupBy('id_materia')
                ->map(function ($filas) {
                    $total      = $filas->count();
                    $reprobados = $filas->where('calificacion_final', '<', 70)->count();
                    $indice     = $total > 0 ? round(($reprobados / $total) * 100, 1) : 0.0;

                    if ($indice >= 50) {
                        $recomendacion = 'Intervención urgente: revisar plan de estudios y asignar tutor';
                    } elseif ($indice >= 35) {
                        $recomendacion = 'Reforzar tutoría en este grupo';
                    } elseif ($indice >= 20) {
                        $recomendacion = 'Monitorear desempeño y ofrecer asesorías adicionales';
                    } else {
                        $recomendacion = 'Seguimiento regular';
                    }

                    return [
                        'materia'            => $filas->first()->nombre_materia,
                        'semestre'           => $filas->first()->semestre,
                        'total_alumnos'      => $total,
                        'reprobados'         => $reprobados,
                        'indice_reprobacion' => $indice,
                        'recomendacion'      => $recomendacion,
                    ];
                })
                ->sortByDesc('indice_reprobacion')
                ->take($limite)
                ->values();

            return response()->json($porMateria);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
