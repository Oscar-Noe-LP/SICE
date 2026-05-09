<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    /**
     * GET /api/reportes/alumnos
     * Alumnos por carrera y estatus
     */
    public function alumnos(Request $request)
    {
        try {
            $query = DB::table('alumno as a')
                ->join('persona as p', 'a.id_persona', '=', 'p.id_persona')
                ->join('carrera as c', 'a.id_carrera', '=', 'c.id_carrera')
                ->select(
                    'a.id_alumno',
                    'a.numero_control',
                    DB::raw("CONCAT(p.nombre,' ',p.apellido_paterno,' ',COALESCE(p.apellido_materno,'')) as nombre_completo"),
                    'c.nombre as carrera',
                    'a.semestre_actual',
                    'a.estatus',
                    'p.curp'
                )
                ->orderBy('c.nombre')
                ->orderBy('p.apellido_paterno');

            if ($request->filled('id_carrera')) {
                $query->where('a.id_carrera', $request->id_carrera);
            }
            if ($request->filled('estatus')) {
                $query->where('a.estatus', $request->estatus);
            }
            if ($request->filled('semestre')) {
                $query->where('a.semestre_actual', $request->semestre);
            }

            $alumnos = $query->get();

            // Resumen por carrera
            $resumen = $alumnos->groupBy('carrera')->map(fn($g) => [
                'carrera' => $g->first()->carrera,
                'total'   => $g->count(),
            ])->values();

            return response()->json([
                'total'   => $alumnos->count(),
                'resumen' => $resumen,
                'datos'   => $alumnos,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/reportes/calificaciones
     * Calificaciones por grupo y período
     */
    public function calificaciones(Request $request)
    {
        try {
            $query = DB::table('calificacion as cal')
                ->join('inscripcion as i', 'cal.id_inscripcion', '=', 'i.id_inscripcion')
                ->join('alumno as a', 'i.id_alumno', '=', 'a.id_alumno')
                ->join('persona as p', 'a.id_persona', '=', 'p.id_persona')
                ->join('grupo as g', 'i.id_grupo', '=', 'g.id_grupo')
                ->join('materia as m', 'g.id_materia', '=', 'm.id_materia')
                ->join('periodo as pe', 'g.id_periodo', '=', 'pe.id_periodo')
                ->select(
                    'a.numero_control',
                    DB::raw("CONCAT(p.nombre,' ',p.apellido_paterno) as nombre_alumno"),
                    'm.nombre as materia',
                    'g.clave_grupo',
                    'pe.nombre_periodo as periodo',
                    'cal.calificacion as calificacion_final',
                    DB::raw("CASE WHEN cal.calificacion >= 70 THEN 'Aprobado' ELSE 'Reprobado' END as resultado")
                )
                ->orderBy('pe.id_periodo', 'desc')
                ->orderBy('m.nombre');

            if ($request->filled('id_grupo')) {
                $query->where('i.id_grupo', $request->id_grupo);
            }
            if ($request->filled('id_periodo')) {
                $query->where('g.id_periodo', $request->id_periodo);
            }
            if ($request->filled('id_materia')) {
                $query->where('g.id_materia', $request->id_materia);
            }

            $datos = $query->get();

            $aprobados  = $datos->where('resultado', 'Aprobado')->count();
            $reprobados = $datos->where('resultado', 'Reprobado')->count();
            $promedio   = $datos->count() > 0 ? round($datos->avg('calificacion_final'), 2) : null;

            return response()->json([
                'total'      => $datos->count(),
                'aprobados'  => $aprobados,
                'reprobados' => $reprobados,
                'promedio'   => $promedio,
                'datos'      => $datos,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/reportes/inscripciones
     * Inscripciones por período y carrera
     */
    public function inscripciones(Request $request)
    {
        try {
            $query = DB::table('inscripcion as i')
                ->join('alumno as a', 'i.id_alumno', '=', 'a.id_alumno')
                ->join('persona as p', 'a.id_persona', '=', 'p.id_persona')
                ->join('carrera as c', 'a.id_carrera', '=', 'c.id_carrera')
                ->join('grupo as g', 'i.id_grupo', '=', 'g.id_grupo')
                ->join('materia as m', 'g.id_materia', '=', 'm.id_materia')
                ->join('periodo as pe', 'g.id_periodo', '=', 'pe.id_periodo')
                ->select(
                    'i.id_inscripcion',
                    'a.numero_control',
                    DB::raw("CONCAT(p.nombre,' ',p.apellido_paterno) as nombre_alumno"),
                    'c.nombre as carrera',
                    'm.nombre as materia',
                    'g.clave_grupo',
                    'pe.nombre_periodo as periodo',
                    'i.fecha_inscripcion',
                    'i.estatus'
                )
                ->orderBy('pe.id_periodo', 'desc')
                ->orderBy('c.nombre');

            if ($request->filled('id_periodo')) {
                $query->where('g.id_periodo', $request->id_periodo);
            }
            if ($request->filled('id_carrera')) {
                $query->where('a.id_carrera', $request->id_carrera);
            }
            if ($request->filled('estatus')) {
                $query->where('i.estatus', $request->estatus);
            }

            $datos = $query->get();

            $resumenPeriodo = $datos->groupBy('periodo')->map(fn($g) => [
                'periodo' => $g->first()->periodo,
                'total'   => $g->count(),
                'activos' => $g->where('estatus', 'Activo')->count(),
            ])->values();

            return response()->json([
                'total'          => $datos->count(),
                'resumen_periodo'=> $resumenPeriodo,
                'datos'          => $datos,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/reportes/grupos
     * Ocupación y estado de grupos
     */
    public function grupos(Request $request)
    {
        try {
            $query = DB::table('grupo as g')
                ->join('materia as m', 'g.id_materia', '=', 'm.id_materia')
                ->join('periodo as pe', 'g.id_periodo', '=', 'pe.id_periodo')
                ->leftJoin('docente as d', 'g.id_docente', '=', 'd.id_docente')
                ->leftJoin('empleado as em', 'd.id_empleado', '=', 'em.id_empleado')
                ->leftJoin('persona as p', 'em.id_persona', '=', 'p.id_persona')
                ->leftJoin('aula as a', 'g.id_aula', '=', 'a.id_aula')
                ->leftJoin(DB::raw('(SELECT id_grupo, COUNT(*) as inscritos FROM inscripcion WHERE estatus = "Activo" GROUP BY id_grupo) as ins'), 'ins.id_grupo', '=', 'g.id_grupo')
                ->select(
                    'g.id_grupo',
                    'g.clave_grupo',
                    'm.nombre as materia',
                    'pe.nombre_periodo as periodo',
                    DB::raw("COALESCE(CONCAT(p.nombre,' ',p.apellido_paterno), 'Sin docente') as docente"),
                    'a.nombre as aula',
                    'g.capacidad',
                    DB::raw('COALESCE(ins.inscritos, 0) as inscritos'),
                    DB::raw('COALESCE(ins.inscritos, 0) / g.capacidad * 100 as porcentaje_ocupacion')
                )
                ->orderBy('pe.id_periodo', 'desc')
                ->orderBy('m.nombre');

            if ($request->filled('id_periodo')) {
                $query->where('g.id_periodo', $request->id_periodo);
            }

            $datos = $query->get();

            return response()->json([
                'total'         => $datos->count(),
                'con_cupo'      => $datos->filter(fn($g) => $g->inscritos < $g->capacidad)->count(),
                'sin_cupo'      => $datos->filter(fn($g) => $g->inscritos >= $g->capacidad)->count(),
                'datos'         => $datos,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/reportes/resumen
     * KPIs generales del sistema
     */
    public function resumen()
    {
        try {
            return response()->json([
                'total_alumnos'       => DB::table('alumno')->count(),
                'alumnos_activos'     => DB::table('alumno')->where('estatus', 'Activo')->count(),
                'total_grupos'        => DB::table('grupo')->count(),
                'total_inscripciones' => DB::table('inscripcion')->count(),
                'total_docentes'      => DB::table('docente')->count(),
                'total_materias'      => DB::table('materia')->count(),
                'total_carreras'      => DB::table('carrera')->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
