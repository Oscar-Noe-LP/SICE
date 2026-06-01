<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class SeguimientoController extends Controller
{
    private function planVigente(int $idCarrera): ?object
    {
        return DB::table('plan_estudio')
            ->where('id_carrera', $idCarrera)
            ->orderByDesc('id_plan')
            ->first();
    }

    private function materiasCursadasDesdeKardex(int $idAlumno)
    {
        return DB::table('kardex as k')
            ->join('detalle_kardex as dk', 'dk.id_kardex', '=', 'k.id_kardex')
            ->where('k.id_alumno', $idAlumno)
            ->select('dk.id_materia', 'dk.calificacion_final', 'dk.estado')
            ->get()
            ->groupBy('id_materia')
            ->map(function ($rows) {
                return $rows->sortByDesc('calificacion_final')->first();
            });
    }

    /**
     * GET /api/seguimiento/{id_alumno}
     * Avance curricular del alumno respecto a su plan de estudios
     */
    public function show(int $id_alumno)
    {
        try {
            $alumno = DB::table('alumno as a')
                ->join('persona as p', 'a.id_persona', '=', 'p.id_persona')
                ->join('carrera as c', 'a.id_carrera', '=', 'c.id_carrera')
                ->where('a.id_alumno', $id_alumno)
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

            if (!$alumno) {
                return response()->json(['error' => 'Alumno no encontrado'], 404);
            }

            $planRow = $this->planVigente($alumno->id_carrera);

            // Todas las materias del plan de estudios de la carrera
            $planMaterias = DB::table('plan_materia as pm')
                ->join('plan_estudio as pe', 'pm.id_plan', '=', 'pe.id_plan')
                ->join('materia as m', 'pm.id_materia', '=', 'm.id_materia')
                ->where('pe.id_carrera', $alumno->id_carrera)
                ->when($planRow, fn($q) => $q->where('pm.id_plan', $planRow->id_plan))
                ->select('m.id_materia', 'm.nombre', 'm.creditos', 'pm.semestre')
                ->orderBy('pm.semestre')
                ->get();

            // Materias ya registradas en kardex/detalle_kardex.
            $cursadas = $this->materiasCursadasDesdeKardex($id_alumno);

            // Calcular estado de cada materia del plan
            $totalCreditos   = 0;
            $creditosAcum    = 0;
            $materiasDetalle = $planMaterias->map(function ($m) use ($cursadas, &$totalCreditos, &$creditosAcum) {
                $totalCreditos += $m->creditos;
                $cursada = $cursadas->get($m->id_materia);

                if (!$cursada) {
                    $estado = 'no_cursada';
                } elseif ($cursada->calificacion_final === null) {
                    $estado = 'pendiente';
                } elseif ($cursada->calificacion_final >= 70) {
                    $estado = 'acreditada';
                    $creditosAcum += $m->creditos;
                } else {
                    $estado = 'reprobada';
                }

                return [
                    'id_materia'        => $m->id_materia,
                    'nombre'            => $m->nombre,
                    'creditos'          => $m->creditos,
                    'semestre'          => $m->semestre,
                    'estado'            => $estado,
                    'calificacion_final'=> $cursada?->calificacion_final !== null ? (float) $cursada->calificacion_final : null,
                ];
            });

            $porcentajeAvance = $totalCreditos > 0
                ? round(($creditosAcum / $totalCreditos) * 100, 1)
                : 0;

            return response()->json([
                'alumno'              => $alumno,
                'creditos_acumulados' => $creditosAcum,
                'creditos_totales'    => $totalCreditos,
                'porcentaje_avance'   => $porcentajeAvance,
                'materias'            => $materiasDetalle->values(),
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/kardex/{numero_control}/avance-curricular
     * Avance curricular buscado por número de control, agrupado por semestre.
     * Formato esperado por AvanceCurricularView.vue.
     */
    public function showByControl(string $numero_control)
    {
        try {
            $alumno = DB::table('alumno as a')
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

            if (!$alumno) {
                return response()->json(['error' => 'Alumno no encontrado'], 404);
            }

            $planRow = $this->planVigente($alumno->id_carrera);

            $planMaterias = DB::table('plan_materia as pm')
                ->join('plan_estudio as pe', 'pm.id_plan', '=', 'pe.id_plan')
                ->join('materia as m', 'pm.id_materia', '=', 'm.id_materia')
                ->where('pe.id_carrera', $alumno->id_carrera)
                ->when($planRow, fn($q) => $q->where('pm.id_plan', $planRow->id_plan))
                ->select('m.id_materia', 'm.clave', 'm.nombre', 'm.creditos', 'pm.semestre')
                ->orderBy('pm.semestre')
                ->orderBy('m.nombre')
                ->get();

            $cursadas = $this->materiasCursadasDesdeKardex($alumno->id_alumno);

            $semestres = [];
            foreach ($planMaterias as $m) {
                $cursada = $cursadas->get($m->id_materia);

                if (!$cursada) {
                    $estado = 'no_cursada';
                } elseif ($cursada->calificacion_final === null) {
                    $estado = 'pendiente';
                } elseif ($cursada->calificacion_final >= 70) {
                    $estado = 'acreditada';
                } else {
                    $estado = 'reprobada';
                }

                $semestres[$m->semestre]['numero']     = $m->semestre;
                $semestres[$m->semestre]['semestre']   = $m->semestre;
                $semestres[$m->semestre]['materias'][] = [
                    'id_materia'         => $m->id_materia,
                    'clave'              => $m->clave,
                    'nombre'             => $m->nombre,
                    'creditos'           => $m->creditos,
                    'estado'             => $estado,
                    'calificacion_final' => $cursada?->calificacion_final !== null ? (float) $cursada->calificacion_final : null,
                    'calificacion'       => $cursada?->calificacion_final !== null ? (float) $cursada->calificacion_final : null,
                ];
            }

            ksort($semestres);

            return response()->json([
                'alumno'       => [
                    'id_alumno'        => $alumno->id_alumno,
                    'numero_control'   => $alumno->numero_control,
                    'no_control'       => $alumno->numero_control,
                    'id_carrera'       => $alumno->id_carrera,
                    'nombre_completo'  => $alumno->nombre_completo,
                    'nombre'           => $alumno->nombre_completo,
                    'carrera'          => $alumno->carrera,
                    'semestre_actual'  => $alumno->semestre_actual,
                    'estatus'          => $alumno->estatus,
                    'plan_estudio'     => $planRow->nombre_plan ?? 'Plan Vigente',
                ],
                'plan_estudios' => array_values($semestres),
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
