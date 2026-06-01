<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class KardexController extends Controller
{
    /**
     * GET /api/kardex/buscar-por-nombre?q=nombre
     * Busca alumno por nombre o apellido y retorna su número de control
     * Respuesta: { numero_control: "25000001", nombre_completo: "Juan Pérez García", carrera: "..." }
     */
    public function buscarPorNombre()
    {
        try {
            $busqueda = trim(request()->query('q', ''));

            // Eliminar espacios repetidos
            $busqueda = preg_replace('/\s+/', ' ', $busqueda);

            if (strlen($busqueda) < 3) {
                return response()->json([
                    'error' => 'La búsqueda debe contener al menos 3 caracteres',
                    'resultados' => []
                ], 400);
            }

            $termino = '%' . $busqueda . '%';

            $alumnos = DB::table('alumno as a')
                ->join('persona as p', 'a.id_persona', '=', 'p.id_persona')
                ->join('carrera as c', 'a.id_carrera', '=', 'c.id_carrera')
                ->where(function ($query) use ($termino) {

                    // Nombre
                    $query->where('p.nombre', 'LIKE', $termino)

                        // Apellido paterno
                        ->orWhere('p.apellido_paterno', 'LIKE', $termino)

                        // Apellido materno
                        ->orWhere('p.apellido_materno', 'LIKE', $termino)

                        // Nombre completo
                        ->orWhereRaw(
                            "CONCAT(
                                p.nombre,
                                ' ',
                                p.apellido_paterno,
                                ' ',
                                COALESCE(p.apellido_materno,'')
                            ) LIKE ?",
                            [$termino]
                        )

                        // Nombre + Apellido paterno
                        ->orWhereRaw(
                            "CONCAT(
                                p.nombre,
                                ' ',
                                p.apellido_paterno
                            ) LIKE ?",
                            [$termino]
                        );
                })
                ->select(
                    'a.numero_control',
                    DB::raw("
                        CONCAT(
                            p.nombre,
                            ' ',
                            p.apellido_paterno,
                            ' ',
                            COALESCE(p.apellido_materno,'')
                        ) as nombre_completo
                    "),
                    'c.nombre as carrera'
                )
                ->orderBy('p.apellido_paterno')
                ->orderBy('p.apellido_materno')
                ->orderBy('p.nombre')
                ->limit(10)
                ->get();

            if ($alumnos->isEmpty()) {
                return response()->json([
                    'error' => 'No se encontraron alumnos con ese nombre o apellido',
                    'resultados' => []
                ], 404);
            }

            return response()->json([
                'resultados' => $alumnos
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'resultados' => []
            ], 500);
        }
    }

    /**
     * GET /api/kardex/{numero_control}
     * Devuelve el kardex completo agrupado por semestre del plan de estudios.
     * Formato esperado por KardexView.vue:
     *   { alumno: { nombre, no_control, carrera, plan_estudio, creditos_acumulados, creditos_totales },
     *     kardex: { semestres: [{ numero, acreditadas, reprobadas, materias: [{clave, nombre, creditos, calificacion, estado}] }] } }
     */
    public function show(string $numero_control)
    {
        try {
            $alumnoRow = DB::table('alumno as a')
                ->join('persona as p', 'a.id_persona', '=', 'p.id_persona')
                ->join('carrera as c', 'a.id_carrera', '=', 'c.id_carrera')
                ->where('a.numero_control', $numero_control)
                ->select(
                    'a.id_alumno',
                    'a.numero_control',
                    'a.id_carrera',
                    DB::raw("CONCAT(p.nombre,' ',p.apellido_paterno,' ',COALESCE(p.apellido_materno,'')) as nombre_completo"),
                    'c.nombre as carrera',
                    'a.semestre_actual',
                    'a.estatus'
                )
                ->first();

            if (!$alumnoRow) {
                return response()->json(['error' => 'Alumno no encontrado'], 404);
            }

            // Obtener plan de estudios de la carrera (nombre del plan y materias por semestre)
            $planRow = DB::table('plan_estudio')
                ->where('id_carrera', $alumnoRow->id_carrera)
                ->orderByDesc('id_plan')
                ->first();

            $planNombre = $planRow->nombre_plan ?? 'Plan Vigente';

            // Materias del plan de estudios agrupadas por semestre
            $planMaterias = DB::table('plan_materia as pm')
                ->join('plan_estudio as pe', 'pm.id_plan', '=', 'pe.id_plan')
                ->join('materia as m', 'pm.id_materia', '=', 'm.id_materia')
                ->where('pe.id_carrera', $alumnoRow->id_carrera)
                ->when($planRow, fn($q) => $q->where('pm.id_plan', $planRow->id_plan))
                ->select('m.id_materia', 'm.clave', 'm.nombre', 'm.creditos', 'pm.semestre')
                ->orderBy('pm.semestre')
                ->orderBy('m.nombre')
                ->get();

            // Materias cursadas por el alumno (mejor calificación si cursó varias veces)
            $cursadas = DB::table('inscripcion as i')
                ->join('grupo as g', 'i.id_grupo', '=', 'g.id_grupo')
                ->leftJoin(
                    DB::raw('(SELECT id_inscripcion, MAX(calificacion) as calificacion FROM calificacion GROUP BY id_inscripcion) as cal'),
                    'cal.id_inscripcion', '=', 'i.id_inscripcion'
                )
                ->where('i.id_alumno', $alumnoRow->id_alumno)
                ->select('g.id_materia', 'cal.calificacion as calificacion_final')
                ->get()
                ->groupBy('id_materia')
                ->map(function ($rows) {
                    // Si cursó la materia más de una vez, usar la mejor calificación
                    return $rows->sortByDesc('calificacion_final')->first();
                });

            // Construir semestres
            $creditosAcum  = 0;
            $creditosTotales = 0;
            $semestres = [];

            foreach ($planMaterias as $m) {
                $creditosTotales += $m->creditos;
                $cursada = $cursadas->get($m->id_materia);

                if (!$cursada) {
                    $estado = 'no_cursada';
                    $calificacion = null;
                } elseif ($cursada->calificacion_final === null) {
                    $estado = 'pendiente';
                    $calificacion = null;
                } elseif ($cursada->calificacion_final >= 70) {
                    $estado = 'acreditada';
                    $calificacion = (float) $cursada->calificacion_final;
                    $creditosAcum += $m->creditos;
                } else {
                    $estado = 'reprobada';
                    $calificacion = (float) $cursada->calificacion_final;
                }

                $sem = $m->semestre;
                if (!isset($semestres[$sem])) {
                    $semestres[$sem] = [
                        'numero'      => $sem,
                        'acreditadas' => 0,
                        'reprobadas'  => 0,
                        'materias'    => [],
                    ];
                }
                if ($estado === 'acreditada') $semestres[$sem]['acreditadas']++;
                if ($estado === 'reprobada')  $semestres[$sem]['reprobadas']++;

                $semestres[$sem]['materias'][] = [
                    'clave'        => $m->clave ?? '',
                    'nombre'       => $m->nombre,
                    'creditos'     => $m->creditos,
                    'calificacion' => $calificacion,
                    'estado'       => $estado,
                ];
            }

            ksort($semestres);

            return response()->json([
                'alumno' => [
                    'nombre'              => $alumnoRow->nombre_completo,
                    'no_control'          => $alumnoRow->numero_control,
                    'carrera'             => $alumnoRow->carrera,
                    'plan_estudio'        => $planNombre,
                    'creditos_acumulados' => $creditosAcum,
                    'creditos_totales'    => $creditosTotales,
                ],
                'kardex' => [
                    'semestres' => array_values($semestres),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // =========================================================================

    /**
     * GET /api/kardex/{numero_control}/pdf
     *
     * Genera el kardex académico en PDF y lo devuelve como descarga.
     * Llamado por KardexDetalleView.vue → exportarPDF().
     *
     * Requiere: composer require barryvdh/laravel-dompdf
     * Vista:    resources/views/kardex/kardex_pdf.blade.php
     */
    public function exportarPDF(string $numero_control)
    {
        try {
            // ── Datos del alumno ──────────────────────────────────────────
            $alumnoRow = DB::table('alumno as a')
                ->join('persona as p', 'a.id_persona', '=', 'p.id_persona')
                ->join('carrera as c', 'a.id_carrera', '=', 'c.id_carrera')
                ->where('a.numero_control', $numero_control)
                ->select(
                    'a.id_alumno',
                    'a.numero_control',
                    'a.id_carrera',
                    DB::raw("CONCAT(p.nombre,' ',p.apellido_paterno,' ',COALESCE(p.apellido_materno,'')) as nombre_completo"),
                    'c.nombre as carrera',
                    'a.semestre_actual',
                    'a.estatus',
                    'p.curp'
                )
                ->first();

            if (!$alumnoRow) {
                return response()->json(['error' => 'Alumno no encontrado'], 404);
            }

            // ── Plan de estudios ──────────────────────────────────────────
            $planRow = DB::table('plan_estudio')
                ->where('id_carrera', $alumnoRow->id_carrera)
                ->orderByDesc('id_plan')
                ->first();

            $planNombre = $planRow->nombre_plan ?? 'Plan Vigente';

            // ── Materias del plan ─────────────────────────────────────────
            $planMaterias = DB::table('plan_materia as pm')
                ->join('plan_estudio as pe', 'pm.id_plan', '=', 'pe.id_plan')
                ->join('materia as m', 'pm.id_materia', '=', 'm.id_materia')
                ->where('pe.id_carrera', $alumnoRow->id_carrera)
                ->when($planRow, fn($q) => $q->where('pm.id_plan', $planRow->id_plan))
                ->select('m.id_materia', 'm.clave', 'm.nombre', 'm.creditos', 'pm.semestre')
                ->orderBy('pm.semestre')
                ->orderBy('m.nombre')
                ->get();

            // ── Calificaciones cursadas ───────────────────────────────────
            $cursadas = DB::table('inscripcion as i')
                ->join('grupo as g', 'i.id_grupo', '=', 'g.id_grupo')
                ->leftJoin(
                    DB::raw('(SELECT id_inscripcion, MAX(calificacion) as calificacion FROM calificacion GROUP BY id_inscripcion) as cal'),
                    'cal.id_inscripcion', '=', 'i.id_inscripcion'
                )
                ->where('i.id_alumno', $alumnoRow->id_alumno)
                ->select('g.id_materia', 'cal.calificacion as calificacion_final')
                ->get()
                ->groupBy('id_materia')
                ->map(fn($rows) => $rows->sortByDesc('calificacion_final')->first());

            // ── Construir semestres (misma lógica que show()) ─────────────
            $creditosAcum    = 0;
            $creditosTotales = 0;
            $semestres       = [];

            foreach ($planMaterias as $m) {
                $creditosTotales += $m->creditos;
                $cursada = $cursadas->get($m->id_materia);

                if (!$cursada) {
                    $estado = 'no_cursada'; $calificacion = null;
                } elseif ($cursada->calificacion_final === null) {
                    $estado = 'pendiente';  $calificacion = null;
                } elseif ($cursada->calificacion_final >= 70) {
                    $estado = 'acreditada';
                    $calificacion = (float) $cursada->calificacion_final;
                    $creditosAcum += $m->creditos;
                } else {
                    $estado = 'reprobada';
                    $calificacion = (float) $cursada->calificacion_final;
                }

                $sem = $m->semestre;
                if (!isset($semestres[$sem])) {
                    $semestres[$sem] = ['numero' => $sem, 'acreditadas' => 0, 'reprobadas' => 0, 'materias' => []];
                }
                if ($estado === 'acreditada') $semestres[$sem]['acreditadas']++;
                if ($estado === 'reprobada')  $semestres[$sem]['reprobadas']++;

                // Promedio del semestre (solo materias con calificación)
                $semestres[$sem]['materias'][] = [
                    'clave'        => $m->clave ?? '',
                    'nombre'       => $m->nombre,
                    'creditos'     => $m->creditos,
                    'calificacion' => $calificacion,
                    'estado'       => $estado,
                ];
            }

            ksort($semestres);

            // Calcular promedio por semestre
            foreach ($semestres as &$sem) {
                $conCalif = array_filter($sem['materias'], fn($mat) => $mat['calificacion'] !== null);
                $sem['promedio'] = count($conCalif) > 0
                    ? round(array_sum(array_column($conCalif, 'calificacion')) / count($conCalif), 2)
                    : null;
            }
            unset($sem);

            // ── Totales globales ──────────────────────────────────────────
            $todasConCalif = array_merge(...array_map(
                fn($s) => array_filter($s['materias'], fn($mat) => $mat['calificacion'] !== null),
                $semestres
            ));
            $promedioGeneral = count($todasConCalif) > 0
                ? round(array_sum(array_column($todasConCalif, 'calificacion')) / count($todasConCalif), 2)
                : null;

            $totalMaterias  = array_sum(array_map(fn($s) => count($s['materias']), $semestres));
            $totalAcreditadas = array_sum(array_column($semestres, 'acreditadas'));
            $totalReprobadas  = array_sum(array_column($semestres, 'reprobadas'));

            // ── Generar PDF ───────────────────────────────────────────────
            $alumno = [
                'nombre'              => $alumnoRow->nombre_completo,
                'no_control'          => $alumnoRow->numero_control,
                'carrera'             => $alumnoRow->carrera,
                'plan_estudio'        => $planNombre,
                'semestre_actual'     => $alumnoRow->semestre_actual,
                'estatus'             => $alumnoRow->estatus,
                'curp'                => $alumnoRow->curp,
                'creditos_acumulados' => $creditosAcum,
                'creditos_totales'    => $creditosTotales,
            ];

            $pdf = Pdf::loadView('kardex.kardex_pdf', [
                'alumno'          => $alumno,
                'semestres'       => array_values($semestres),
                'promedioGeneral' => $promedioGeneral,
                'totalMaterias'   => $totalMaterias,
                'totalAcreditadas'=> $totalAcreditadas,
                'totalReprobadas' => $totalReprobadas,
                'fechaGeneracion' => now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY'),
            ])->setPaper('letter', 'portrait');

            return $pdf->download("kardex_{$numero_control}.pdf");

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
