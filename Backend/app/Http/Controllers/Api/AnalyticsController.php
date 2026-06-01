<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
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

    // D1 — GET /api/analytics/aprobados-reprobados
    public function aprobadosReprobados(Request $request)
    {
        $carreraId = $request->query('carrera_id');
        $periodoId = $request->query('periodo_id');

        $periodo = $periodoId ? DB::table('periodo')->where('id_periodo', $periodoId)->first() : null;
        if ($periodoId && !$periodo) return response()->json(['error' => 'Periodo no encontrado.'], 404);

        $carrera = $carreraId ? DB::table('carrera')->where('id_carrera', $carreraId)->first() : null;
        if ($carreraId && !$carrera) return response()->json(['error' => 'Carrera no encontrada.'], 404);

        try {
            $query = DB::table('inscripcion as i')
                ->join('grupo as g',   'i.id_grupo',   '=', 'g.id_grupo')
                ->join('materia as m', 'g.id_materia', '=', 'm.id_materia');

            if ($periodoId) $query->where('g.id_periodo', $periodoId);
            if ($carreraId) {
                $query->join('plan_materia as pm', 'm.id_materia', '=', 'pm.id_materia')
                      ->join('plan_estudio as pe',  'pm.id_plan',   '=', 'pe.id_plan')
                      ->where('pe.id_carrera', $carreraId);
            }

            $resultados = $query
                ->leftJoin(DB::raw($this->subqueryCalPonderada()), 'cal_pond.id_inscripcion', '=', 'i.id_inscripcion')
                ->select('m.id_materia', 'm.nombre as nombre_materia', 'i.id_alumno',
                    DB::raw('COALESCE(cal_pond.calificacion_ponderada, 0) as calificacion_final'))
                ->get();

            if ($resultados->isEmpty()) {
                return response()->json([
                    'carrera' => $carrera ? $carrera->nombre : 'Todas',
                    'periodo' => $periodo ? $periodo->nombre_periodo : 'Todos',
                    'total_alumnos' => 0, 'aprobados' => 0, 'reprobados' => 0,
                    'porcentaje_aprobacion' => 0.0, 'por_materia' => [],
                ]);
            }

            $totalAlumnos    = $resultados->pluck('id_alumno')->unique()->count();
            $aprobadosTotal  = $resultados->where('calificacion_final', '>=', 70)->count();
            $reprobadosTotal = $resultados->where('calificacion_final', '<',  70)->count();
            $totalRegistros  = $resultados->count();
            $porcentaje      = $totalRegistros > 0 ? round(($aprobadosTotal / $totalRegistros) * 100, 1) : 0.0;

            $porMateria = $resultados->groupBy('id_materia')->map(function ($filas) {
                $ap = $filas->where('calificacion_final', '>=', 70)->count();
                $re = $filas->where('calificacion_final', '<',  70)->count();
                $to = $filas->count();
                return ['materia' => $filas->first()->nombre_materia, 'aprobados' => $ap,
                        'reprobados' => $re, 'porcentaje' => $to > 0 ? round(($ap/$to)*100,1) : 0.0];
            })->sortByDesc('reprobados')->values();

            return response()->json([
                'carrera' => $carrera ? $carrera->nombre : 'Todas',
                'periodo' => $periodo ? $periodo->nombre_periodo : 'Todos',
                'total_alumnos' => $totalAlumnos, 'aprobados' => $aprobadosTotal,
                'reprobados' => $reprobadosTotal, 'porcentaje_aprobacion' => $porcentaje,
                'por_materia' => $porMateria,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // D2 — GET /api/analytics/tronco-comun
    public function troncoComun(Request $request)
    {
        $periodoId = $request->query('periodo_id');
        if (!$periodoId) return response()->json(['error' => 'El parámetro periodo_id es requerido.'], 422);

        $periodo = DB::table('periodo')->where('id_periodo', $periodoId)->first();
        if (!$periodo) return response()->json(['error' => 'Periodo no encontrado.'], 404);

        try {
            $resultados = DB::table('inscripcion as i')
                ->join('grupo as g',        'i.id_grupo',   '=', 'g.id_grupo')
                ->join('materia as m',      'g.id_materia', '=', 'm.id_materia')
                ->join('plan_materia as pm','m.id_materia', '=', 'pm.id_materia')
                ->where('g.id_periodo', $periodoId)
                ->whereIn('pm.semestre', [1, 2])
                ->leftJoin(DB::raw($this->subqueryCalPonderada()), 'cal_pond.id_inscripcion', '=', 'i.id_inscripcion')
                ->select('m.id_materia', 'm.nombre as nombre_materia', 'pm.semestre',
                    DB::raw('COALESCE(cal_pond.calificacion_ponderada, 0) as calificacion_final'))
                ->get();

            if ($resultados->isEmpty()) return response()->json([]);

            $porMateria = $resultados->groupBy('id_materia')->map(function ($filas) {
                $total = $filas->count();
                $ap    = $filas->where('calificacion_final', '>=', 70)->count();
                $re    = $total - $ap;
                $pct   = $total > 0 ? round(($ap/$total)*100, 1) : 0.0;
                $prom  = $total > 0 ? round($filas->avg('calificacion_final'), 1) : 0.0;
                return ['materia' => $filas->first()->nombre_materia, 'semestre' => $filas->first()->semestre,
                        'es_tronco_comun' => true, 'promedio_general' => $prom,
                        'porcentaje_aprobacion' => $pct, 'aprobados' => $ap, 'reprobados' => $re,
                        'en_riesgo' => $pct < 70.0];
            })->sortBy('semestre')->values();

            return response()->json($porMateria);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // D3 — GET /api/analytics/materias-riesgo
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
                ->leftJoin(DB::raw($this->subqueryCalPonderada()), 'cal_pond.id_inscripcion', '=', 'i.id_inscripcion')
                ->select('m.id_materia', 'm.nombre as nombre_materia', 'pm.semestre',
                    DB::raw('COALESCE(cal_pond.calificacion_ponderada, 0) as calificacion_final'))
                ->get();

            if ($resultados->isEmpty()) return response()->json([]);

            $porMateria = $resultados->groupBy('id_materia')->map(function ($filas) {
                $total  = $filas->count();
                $repro  = $filas->where('calificacion_final', '<', 70)->count();
                $indice = $total > 0 ? round(($repro/$total)*100, 1) : 0.0;
                if ($indice >= 50)      $rec = 'Intervención urgente: revisar plan de estudios y asignar tutor';
                elseif ($indice >= 35)  $rec = 'Reforzar tutoría en este grupo';
                elseif ($indice >= 20)  $rec = 'Monitorear desempeño y ofrecer asesorías adicionales';
                else                    $rec = 'Seguimiento regular';
                return ['materia' => $filas->first()->nombre_materia, 'semestre' => $filas->first()->semestre,
                        'total_alumnos' => $total, 'reprobados' => $repro,
                        'indice_reprobacion' => $indice, 'recomendacion' => $rec];
            })->sortByDesc('indice_reprobacion')->take($limite)->values();

            return response()->json($porMateria);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
