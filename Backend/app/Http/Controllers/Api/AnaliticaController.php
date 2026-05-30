<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alumno;
use App\Models\Carrera;
use App\Models\Materia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnaliticaController extends Controller
{
    public function resumen(Request $request)
    {
        try {
            $periodo = $request->get('periodo');
            $carreraId = $request->get('carrera_id');

            // 1. Estadísticas generales de alumnos
            $queryAlumnos = Alumno::query();
            if ($carreraId) {
                $queryAlumnos->where('id_carrera', $carreraId);
            }

            $totalAlumnos = $queryAlumnos->count();
            $alumnosActivos = $queryAlumnos->where('estatus', 'Activo')->count();

            // 2. Promedio institucional general
            $promedioGeneral = DB::table('detalle_kardex as dk')
                ->join('kardex as k', 'dk.id_kardex', '=', 'k.id_kardex')
                ->join('alumno as a', 'k.id_alumno', '=', 'a.id_alumno')
                ->when($carreraId, function ($q) use ($carreraId) {
                    return $q->where('a.id_carrera', $carreraId);
                })
                ->when($periodo, function ($q) use ($periodo) {
                    return $q->whereYear('dk.fecha_registro', $periodo);
                })
                ->whereNotNull('dk.calificacion_final')
                ->avg('dk.calificacion_final');

            // 3. Tasas de reprobación y aprobación
            $totalesMaterias = DB::table('detalle_kardex as dk')
                ->join('kardex as k', 'dk.id_kardex', '=', 'k.id_kardex')
                ->join('alumno as a', 'k.id_alumno', '=', 'a.id_alumno')
                ->when($carreraId, function ($q) use ($carreraId) {
                    return $q->where('a.id_carrera', $carreraId);
                })
                ->when($periodo, function ($q) use ($periodo) {
                    return $q->whereYear('dk.fecha_registro', $periodo);
                })
                ->count();

            $reprobadas = DB::table('detalle_kardex as dk')
                ->join('kardex as k', 'dk.id_kardex', '=', 'k.id_kardex')
                ->join('alumno as a', 'k.id_alumno', '=', 'a.id_alumno')
                ->when($carreraId, function ($q) use ($carreraId) {
                    return $q->where('a.id_carrera', $carreraId);
                })
                ->when($periodo, function ($q) use ($periodo) {
                    return $q->whereYear('dk.fecha_registro', $periodo);
                })
                ->where('dk.calificacion_final', '<', 70)
                ->whereNotNull('dk.calificacion_final')
                ->count();

            $aprobadas = DB::table('detalle_kardex as dk')
                ->join('kardex as k', 'dk.id_kardex', '=', 'k.id_kardex')
                ->join('alumno as a', 'k.id_alumno', '=', 'a.id_alumno')
                ->when($carreraId, function ($q) use ($carreraId) {
                    return $q->where('a.id_carrera', $carreraId);
                })
                ->when($periodo, function ($q) use ($periodo) {
                    return $q->whereYear('dk.fecha_registro', $periodo);
                })
                ->where('dk.calificacion_final', '>=', 70)
                ->whereNotNull('dk.calificacion_final')
                ->count();

            $tasaReprobacion = $totalesMaterias > 0
                ? round(($reprobadas / $totalesMaterias) * 100, 2)
                : 0;

            $tasaAprobacion = $totalesMaterias > 0
                ? round(($aprobadas / $totalesMaterias) * 100, 2)
                : 0;

            // 4. Distribución por rangos de calificación
            $rangosCalificaciones = [
                'excelente' => DB::table('detalle_kardex as dk')
                    ->join('kardex as k', 'dk.id_kardex', '=', 'k.id_kardex')
                    ->join('alumno as a', 'k.id_alumno', '=', 'a.id_alumno')
                    ->when($carreraId, function ($q) use ($carreraId) {
                        return $q->where('a.id_carrera', $carreraId);
                    })
                    ->when($periodo, function ($q) use ($periodo) {
                        return $q->whereYear('dk.fecha_registro', $periodo);
                    })
                    ->whereBetween('dk.calificacion_final', [90, 100])
                    ->count(),
                'notable' => DB::table('detalle_kardex as dk')
                    ->join('kardex as k', 'dk.id_kardex', '=', 'k.id_kardex')
                    ->join('alumno as a', 'k.id_alumno', '=', 'a.id_alumno')
                    ->when($carreraId, function ($q) use ($carreraId) {
                        return $q->where('a.id_carrera', $carreraId);
                    })
                    ->when($periodo, function ($q) use ($periodo) {
                        return $q->whereYear('dk.fecha_registro', $periodo);
                    })
                    ->whereBetween('dk.calificacion_final', [80, 89])
                    ->count(),
                'bueno' => DB::table('detalle_kardex as dk')
                    ->join('kardex as k', 'dk.id_kardex', '=', 'k.id_kardex')
                    ->join('alumno as a', 'k.id_alumno', '=', 'a.id_alumno')
                    ->when($carreraId, function ($q) use ($carreraId) {
                        return $q->where('a.id_carrera', $carreraId);
                    })
                    ->when($periodo, function ($q) use ($periodo) {
                        return $q->whereYear('dk.fecha_registro', $periodo);
                    })
                    ->whereBetween('dk.calificacion_final', [70, 79])
                    ->count(),
                'reprobado' => $reprobadas
            ];

            $data = [
                'resumen_general' => [
                    'total_alumnos' => $totalAlumnos,
                    'alumnos_activos' => $alumnosActivos,
                    'tasa_actividad' => $totalAlumnos > 0
                        ? round(($alumnosActivos / $totalAlumnos) * 100, 2)
                        : 0,
                    'promedio_institucional' => round($promedioGeneral ?? 0, 2),
                    'tasa_aprobacion' => $tasaAprobacion,
                    'tasa_reprobacion' => $tasaReprobacion,
                ],
                'distribucion_calificaciones' => $rangosCalificaciones,
                'filtros_aplicados' => [
                    'periodo' => $periodo ?? 'Todos',
                    'carrera_id' => $carreraId ?? 'Todas'
                ],
                'fecha_actualizacion' => now()->format('Y-m-d H:i:s')
            ];

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'KPIs generales obtenidos exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener KPIs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /analitica/carreras - Rendimiento por carrera
     */
    public function rendimientoPorCarreras(Request $request)
    {
        try {
            $periodo = $request->get('periodo');
            $top = $request->get('top', 10);
            $carreraId = $request->get('carrera_id');

            // Obtener carreras activas - SIN la relación departamento
            $query = Carrera::where('estatus', 1);

            if ($carreraId) {
                $query->where('id_carrera', $carreraId);
            }

            $carreras = $query->get();

            $rendimientoCarreras = [];
            $totalGeneral = 0;
            $contadorCarreras = 0;

            foreach ($carreras as $carrera) {
                // Promedio general de la carrera
                $promedio = DB::table('detalle_kardex as dk')
                    ->join('kardex as k', 'dk.id_kardex', '=', 'k.id_kardex')
                    ->join('alumno as a', 'k.id_alumno', '=', 'a.id_alumno')
                    ->where('a.id_carrera', $carrera->id_carrera)
                    ->when($periodo, function ($q) use ($periodo) {
                        return $q->whereYear('dk.fecha_registro', $periodo);
                    })
                    ->whereNotNull('dk.calificacion_final')
                    ->avg('dk.calificacion_final');

                if (!$promedio) continue;

                // Total de materias cursadas
                $totalMaterias = DB::table('detalle_kardex as dk')
                    ->join('kardex as k', 'dk.id_kardex', '=', 'k.id_kardex')
                    ->join('alumno as a', 'k.id_alumno', '=', 'a.id_alumno')
                    ->where('a.id_carrera', $carrera->id_carrera)
                    ->when($periodo, function ($q) use ($periodo) {
                        return $q->whereYear('dk.fecha_registro', $periodo);
                    })
                    ->count();

                // Materias reprobadas
                $reprobadas = DB::table('detalle_kardex as dk')
                    ->join('kardex as k', 'dk.id_kardex', '=', 'k.id_kardex')
                    ->join('alumno as a', 'k.id_alumno', '=', 'a.id_alumno')
                    ->where('a.id_carrera', $carrera->id_carrera)
                    ->when($periodo, function ($q) use ($periodo) {
                        return $q->whereYear('dk.fecha_registro', $periodo);
                    })
                    ->where('dk.calificacion_final', '<', 70)
                    ->whereNotNull('dk.calificacion_final')
                    ->count();

                $tasaReprobacion = $totalMaterias > 0
                    ? round(($reprobadas / $totalMaterias) * 100, 2)
                    : 0;

                // Total de alumnos activos
                $totalAlumnos = Alumno::where('id_carrera', $carrera->id_carrera)
                    ->where('estatus', 'Activo')
                    ->count();

                // Obtener nombre del departamento si existe la tabla
                $departamentoNombre = 'N/A';
                if ($carrera->id_departamento) {
                    $departamento = DB::table('departamento')
                        ->where('id_departamento', $carrera->id_departamento)
                        ->first();
                    $departamentoNombre = $departamento->nombre ?? 'N/A';
                }

                $rendimientoCarreras[] = [
                    'id_carrera' => $carrera->id_carrera,
                    'nombre_carrera' => $carrera->nombre,
                    'departamento' => $departamentoNombre,
                    'total_alumnos' => $totalAlumnos,
                    'promedio_general' => round($promedio, 2),
                    'tasa_reprobacion' => $tasaReprobacion,
                    'tasa_aprobacion' => 100 - $tasaReprobacion,
                    'materias_cursadas' => $totalMaterias,
                    'materias_aprobadas' => $totalMaterias - $reprobadas,
                    'materias_reprobadas' => $reprobadas
                ];

                $totalGeneral += $promedio;
                $contadorCarreras++;
            }

            // Ordenar por promedio general
            usort($rendimientoCarreras, function ($a, $b) {
                return $b['promedio_general'] <=> $a['promedio_general'];
            });

            // Top y Bottom carreras
            $topCarreras = array_slice($rendimientoCarreras, 0, $top);
            $bottomCarreras = array_slice(array_reverse($rendimientoCarreras), 0, $top);

            return response()->json([
                'success' => true,
                'data' => [
                    'resumen_general' => [
                        'total_carreras_analizadas' => $contadorCarreras,
                        'promedio_institucional' => $contadorCarreras > 0
                            ? round($totalGeneral / $contadorCarreras, 2)
                            : 0,
                    ],
                    'top_carreras_rendimiento' => $topCarreras,
                    'bottom_carreras_rendimiento' => $bottomCarreras,
                    'todas_carreras' => $rendimientoCarreras,
                    'filtros' => [
                        'periodo' => $periodo ?? 'Todos',
                        'carrera_id' => $carreraId ?? 'Todas',
                        'top' => $top
                    ]
                ],
                'message' => 'Rendimiento por carreras obtenido exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener rendimiento por carreras: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /analitica/materias-criticas - Materias con mayor índice de reprobación
     */
    public function materiasCriticas(Request $request)
    {
        try {
            $periodo = $request->get('periodo');
            $carreraId = $request->get('carrera_id');
            $limite = $request->get('limite', 15);
            $umbralReprobacion = $request->get('umbral', 30); // % mínimo para considerar crítica

            // Consulta de materias con altos índices de reprobación
            $materiasCriticas = DB::table('detalle_kardex as dk')
                ->join('materia as m', 'dk.id_materia', '=', 'm.id_materia')
                ->join('kardex as k', 'dk.id_kardex', '=', 'k.id_kardex')
                ->join('alumno as a', 'k.id_alumno', '=', 'a.id_alumno')
                ->leftJoin('carrera as c', 'a.id_carrera', '=', 'c.id_carrera')
                ->when($carreraId, function ($q) use ($carreraId) {
                    return $q->where('a.id_carrera', $carreraId);
                })
                ->when($periodo, function ($q) use ($periodo) {
                    return $q->whereYear('dk.fecha_registro', $periodo);
                })
                ->select(
                    'm.id_materia',
                    'm.clave',
                    'm.nombre as materia_nombre',
                    'm.creditos',
                    DB::raw('c.nombre as carrera_nombre'),
                    DB::raw('COUNT(*) as total_inscritos'),
                    DB::raw('SUM(CASE WHEN dk.calificacion_final < 70 THEN 1 ELSE 0 END) as total_reprobados'),
                    DB::raw('SUM(CASE WHEN dk.calificacion_final >= 70 THEN 1 ELSE 0 END) as total_aprobados'),
                    DB::raw('AVG(dk.calificacion_final) as promedio_general'),
                    DB::raw('MIN(dk.calificacion_final) as calificacion_minima'),
                    DB::raw('MAX(dk.calificacion_final) as calificacion_maxima')
                )
                ->whereNotNull('dk.calificacion_final')
                ->groupBy('m.id_materia', 'm.clave', 'm.nombre', 'm.creditos', 'c.nombre')
                ->havingRaw('(SUM(CASE WHEN dk.calificacion_final < 70 THEN 1 ELSE 0 END) / COUNT(*)) * 100 >= ?', [$umbralReprobacion])
                ->orderByRaw('(SUM(CASE WHEN dk.calificacion_final < 70 THEN 1 ELSE 0 END) / COUNT(*)) DESC')
                ->limit($limite)
                ->get();

            // Estadísticas adicionales por materia
            $materiasDetalle = [];
            foreach ($materiasCriticas as $materia) {
                $tasaReprobacion = $materia->total_inscritos > 0
                    ? round(($materia->total_reprobados / $materia->total_inscritos) * 100, 2)
                    : 0;

                $tasaAprobacion = 100 - $tasaReprobacion;

                $nivelCriticidad = $this->calcularNivelCriticidad($tasaReprobacion);

                $materiasDetalle[] = [
                    'id_materia' => $materia->id_materia,
                    'clave' => $materia->clave,
                    'nombre' => $materia->materia_nombre,
                    'creditos' => $materia->creditos,
                    'carrera' => $materia->carrera_nombre ?? 'Múltiples carreras',
                    'estadisticas' => [
                        'total_inscritos' => $materia->total_inscritos,
                        'total_aprobados' => $materia->total_aprobados,
                        'total_reprobados' => $materia->total_reprobados,
                        'tasa_aprobacion' => $tasaAprobacion,
                        'tasa_reprobacion' => $tasaReprobacion,
                        'promedio_general' => round($materia->promedio_general, 2),
                        'calificacion_minima' => round($materia->calificacion_minima, 2),
                        'calificacion_maxima' => round($materia->calificacion_maxima, 2),
                    ],
                    'nivel_criticidad' => $nivelCriticidad,
                ];
            }

            // Estadísticas globales
            $totalMateriasCriticas = count($materiasDetalle);
            $promedioReprobacionCriticas = $totalMateriasCriticas > 0
                ? collect($materiasDetalle)->avg('estadisticas.tasa_reprobacion')
                : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'resumen' => [
                        'total_materias_criticas' => $totalMateriasCriticas,
                        'promedio_tasa_reprobacion' => round($promedioReprobacionCriticas, 2),
                        'umbral_criticidad' => $umbralReprobacion . '%',
                        'recomendaciones' => $this->generarRecomendaciones($materiasDetalle)
                    ],
                    'materias_criticas' => $materiasDetalle,
                    'filtros_aplicados' => [
                        'periodo' => $periodo ?? 'Todos',
                        'carrera_id' => $carreraId ?? 'Todas',
                        'umbral_reprobacion' => $umbralReprobacion . '%',
                        'limite' => $limite
                    ]
                ],
                'message' => 'Materias críticas identificadas exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener materias críticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Método auxiliar para calcular nivel de criticidad
     */
    private function calcularNivelCriticidad($tasaReprobacion)
    {
        if ($tasaReprobacion >= 60) {
            return [
                'nivel' => 'Crítico',
                'color' => '#dc3545',
                'accion' => 'Intervención inmediata requerida',
                'prioridad' => 1
            ];
        } elseif ($tasaReprobacion >= 40) {
            return [
                'nivel' => 'Alto',
                'color' => '#fd7e14',
                'accion' => 'Revisar metodología de enseñanza',
                'prioridad' => 2
            ];
        } elseif ($tasaReprobacion >= 30) {
            return [
                'nivel' => 'Moderado',
                'color' => '#ffc107',
                'accion' => 'Monitoreo y seguimiento',
                'prioridad' => 3
            ];
        } else {
            return [
                'nivel' => 'Bajo',
                'color' => '#28a745',
                'accion' => 'Mantener estrategias actuales',
                'prioridad' => 4
            ];
        }
    }

    /**
     * Método auxiliar para generar recomendaciones
     */
    private function generarRecomendaciones($materiasCriticas)
    {
        $recomendaciones = [];

        if (count($materiasCriticas) > 5) {
            $recomendaciones[] = "Se han identificado más de 5 materias con alta reprobación. Se recomienda una revisión curricular integral.";
        }

        $promedioReprobacion = collect($materiasCriticas)->avg('estadisticas.tasa_reprobacion');
        if ($promedioReprobacion > 50) {
            $recomendaciones[] = "El promedio de reprobación en materias críticas supera el 50%. Considere implementar programas de tutorías y asesorías académicas.";
        }

        $materiasMuyCriticas = collect($materiasCriticas)->filter(function ($m) {
            return $m['nivel_criticidad']['nivel'] === 'Crítico';
        });

        if ($materiasMuyCriticas->count() > 0) {
            $recomendaciones[] = "Las siguientes materias requieren atención inmediata: " .
                $materiasMuyCriticas->pluck('nombre')->implode(', ');
        }

        if (empty($recomendaciones)) {
            $recomendaciones[] = "Aunque se identificaron áreas de oportunidad, los índices de reprobación están dentro de rangos manejables. Continúe con el monitoreo constante.";
        }

        return $recomendaciones;
    }
}
