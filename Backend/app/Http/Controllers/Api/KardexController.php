<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class KardexController extends Controller
{
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
}
