<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class KardexController extends Controller
{
    /**
     * GET /api/kardex/{id_alumno}
     * Devuelve el kardex completo calculado desde inscripciones y calificaciones
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
                    DB::raw("CONCAT(p.nombre, ' ', p.apellido_paterno, ' ', COALESCE(p.apellido_materno,'')) as nombre_completo"),
                    'c.nombre as carrera',
                    'a.semestre_actual',
                    'a.estatus'
                )
                ->first();

            if (!$alumno) {
                return response()->json(['error' => 'Alumno no encontrado'], 404);
            }

            // Materias cursadas con calificación
            $materias = DB::table('inscripcion as i')
                ->join('grupo as g', 'i.id_grupo', '=', 'g.id_grupo')
                ->join('materia as m', 'g.id_materia', '=', 'm.id_materia')
                ->join('periodo as pe', 'g.id_periodo', '=', 'pe.id_periodo')
                ->leftJoin('calificacion as cal', function ($join) {
                    $join->on('cal.id_inscripcion', '=', 'i.id_inscripcion');
                })
                ->where('i.id_alumno', $id_alumno)
                ->select(
                    'm.id_materia',
                    'm.nombre as nombre_materia',
                    'm.creditos',
                    'pe.nombre_periodo as periodo',
                    'pe.id_periodo',
                    'cal.calificacion as calificacion_final',
                    'i.estatus as estatus_inscripcion',
                    DB::raw("CASE
                        WHEN cal.calificacion IS NULL THEN 'pendiente'
                        WHEN cal.calificacion >= 70 THEN 'acreditada'
                        ELSE 'reprobada'
                    END as estado")
                )
                ->orderBy('pe.id_periodo')
                ->orderBy('m.nombre')
                ->get();

            // Cálculo de créditos y promedio
            $acreditadas    = $materias->where('estado', 'acreditada');
            $creditosAcum   = $acreditadas->sum('creditos');
            $promedioGeneral = $acreditadas->count() > 0
                ? round($acreditadas->avg('calificacion_final'), 2)
                : null;

            // Agrupar por período
            $porPeriodo = $materias->groupBy('periodo')->map(function ($mats, $periodo) {
                return [
                    'periodo'  => $periodo,
                    'materias' => $mats->values(),
                ];
            })->values();

            return response()->json([
                'alumno'           => $alumno,
                'promedio_general' => $promedioGeneral,
                'creditos_acumulados' => $creditosAcum,
                'total_materias'   => $materias->count(),
                'acreditadas'      => $acreditadas->count(),
                'reprobadas'       => $materias->where('estado', 'reprobada')->count(),
                'pendientes'       => $materias->where('estado', 'pendiente')->count(),
                'historial'        => $porPeriodo,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
