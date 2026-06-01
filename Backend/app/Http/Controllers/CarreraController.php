<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CarreraController extends Controller
{
    // ─────────────────────────────────────────────
    // PANTALLA 1: Obtener todas las carreras con contadores y relaciones
    public function index()
    {
        $carreras = DB::table('carrera as c')
            ->leftJoin('departamento as d', 'c.id_departamento', '=', 'd.id_departamento')
            ->leftJoin('nivel_carrera as n', 'c.id_nivel', '=', 'n.id_nivel')
            ->select(
                'c.id_carrera',
                'c.nombre',
                'c.id_departamento',
                'c.id_nivel',
                'c.estatus',
                'd.nombre as departamento_nombre',
                'n.nombre_nivel',

                // 1. Alumnos Activos totales inscritos en esta carrera
                DB::raw('(SELECT COUNT(*) FROM alumno as a
                          WHERE a.id_carrera = c.id_carrera AND a.estatus = "Activo") as total_alumnos'),

                // 2. Grupos abiertos que pertenecen al plan de estudios de esta carrera
                DB::raw('(SELECT COUNT(DISTINCT g.id_grupo) FROM grupo as g
                          JOIN materia as m ON g.id_materia = m.id_materia
                          JOIN plan_materia as pm ON m.id_materia = pm.id_materia
                          JOIN plan_estudio as pe ON pm.id_plan = pe.id_plan
                          WHERE pe.id_carrera = c.id_carrera AND g.estatus = 1) as total_grupos'),

                // 3. Docentes únicos que imparten clases en esta carrera
                DB::raw('(SELECT COUNT(DISTINCT g.id_docente) FROM grupo as g
                          JOIN materia as m ON g.id_materia = m.id_materia
                          JOIN plan_materia as pm ON m.id_materia = pm.id_materia
                          JOIN plan_estudio as pe ON pm.id_plan = pe.id_plan
                          WHERE pe.id_carrera = c.id_carrera AND g.id_docente IS NOT NULL AND g.estatus = 1) as total_docentes')
            )
            ->get()
            ->map(function ($c) {
                return [
                    'id_carrera'      => $c->id_carrera,
                    'id'              => $c->id_carrera,
                    'nombre'          => $c->nombre,
                    'clave'           => strtoupper(implode('', array_map(
                        fn($w) => substr($w, 0, 1),
                        array_filter(
                            explode(' ', preg_replace('/\b(EN|DE|LA|LOS|LAS|Y|A|DEL)\b/i', '', $c->nombre)),
                            fn($w) => strlen(trim($w)) > 0
                        )
                    ))),
                    'id_departamento' => $c->id_departamento,
                    'id_nivel'        => $c->id_nivel,
                    'estatus'         => $c->estatus,
                    // Métricas para la Pantalla 1
                    'total_alumnos'   => (int) $c->total_alumnos,
                    'total_grupos'    => (int) $c->total_grupos,
                    'totalGrupos'     => (int) $c->total_grupos,
                    'total_docentes'  => (int) $c->total_docentes,
                    // Relaciones estructuradas para formularios de Vue
                    'departamento'    => [
                        'nombre' => $c->departamento_nombre
                    ],
                    'nivel'           => [
                        'nombre_nivel' => $c->nombre_nivel
                    ]
                ];
            });

        return response()->json($carreras);
    }

    // ─────────────────────────────────────────────
    // Crear carrera
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|max:150',
            'id_departamento' => 'required|exists:departamento,id_departamento',
            'id_nivel' => 'required|exists:nivel_carrera,id_nivel',
            'estatus' => 'boolean'
        ]);

        $id = DB::table('carrera')->insertGetId([
            'nombre' => $request->nombre,
            'id_departamento' => $request->id_departamento,
            'id_nivel' => $request->id_nivel,
            'estatus' => $request->estatus ?? 1
        ]);

        return response()->json([
            'message' => 'Carrera creada',
            'id' => $id
        ], 201);
    }

    // ─────────────────────────────────────────────
    // Actualizar carrera
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|max:150',
            'id_departamento' => 'required|exists:departamento,id_departamento',
            'id_nivel' => 'required|exists:nivel_carrera,id_nivel',
            'estatus' => 'boolean'
        ]);

        DB::table('carrera')
            ->where('id_carrera', $id)
            ->update([
                'nombre' => $request->nombre,
                'id_departamento' => $request->id_departamento,
                'id_nivel' => $request->id_nivel,
                'estatus' => $request->estatus
            ]);

        return response()->json([
            'message' => 'Carrera actualizada'
        ]);
    }

    // ─────────────────────────────────────────────
    // Eliminar carrera
    public function destroy($id)
    {
        DB::table('carrera')
            ->where('id_carrera', $id)
            ->delete();

        return response()->json([
            'message' => 'Carrera eliminada'
        ]);
    }

    // ─────────────────────────────────────────────
    // PANTALLA 2: Drill-down de grupos por carrera
    // GET /api/carreras/{id}/grupos
    public function grupos($id)
    {
        $carrera = DB::table('carrera')
            ->where('id_carrera', $id)
            ->select('id_carrera', 'nombre')
            ->first();

        if (!$carrera) {
            return response()->json(['message' => 'Carrera no encontrada'], 404);
        }

        // Obtener materias del plan Y su semestre al mismo tiempo
        $materiasConSemestre = DB::table('plan_estudio as pe')
            ->join('plan_materia as pm', 'pe.id_plan', '=', 'pm.id_plan')
            ->where('pe.id_carrera', $id)
            ->where('pe.estatus', 1)
            ->select('pm.id_materia', 'pm.semestre')
            ->get()
            ->keyBy('id_materia');  // ← indexado por id_materia para búsqueda rápida

        if ($materiasConSemestre->isEmpty()) {
            return response()->json([
                'carrera' => $carrera->nombre,
                'grupos'  => []
            ]);
        }

        $idMaterias = $materiasConSemestre->keys();

        $grupos = DB::table('grupo as g')
            ->join('materia as m', 'g.id_materia', '=', 'm.id_materia')
            ->leftJoin('docente as d', 'g.id_docente', '=', 'd.id_docente')
            ->leftJoin('empleado as e', 'd.id_empleado', '=', 'e.id_empleado')
            ->leftJoin('persona as p', 'e.id_persona', '=', 'p.id_persona')
            ->whereIn('g.id_materia', $idMaterias)
            ->where('g.estatus', 1)
            ->select(
                'g.id_grupo',
                'g.id_materia',   // ← auxiliar para buscar semestre
                'g.clave_grupo',
                'm.nombre as materia',
                DB::raw("IF(
                    p.id_persona IS NOT NULL,
                    UPPER(CONCAT(p.apellido_paterno, ' ', p.apellido_materno, ' ', p.nombre)),
                    'Sin asignar'
                ) as docente"),
                'g.capacidad',
                DB::raw("(
                    SELECT COUNT(*)
                    FROM inscripcion AS i
                    WHERE i.id_grupo = g.id_grupo
                    AND i.estatus = 'Activo'
                ) as inscritos")
            )
            ->orderBy('m.nombre')
            ->get()
            ->map(function ($g) use ($materiasConSemestre) {
                $g->semestre = $materiasConSemestre[$g->id_materia]->semestre ?? null;
                unset($g->id_materia);  // limpiar campo auxiliar
                return $g;
            })
            ->sortBy('semestre')   // ← ordenar por semestre en PHP
            ->values();            // ← reindexar el array

        return response()->json([
            'carrera' => $carrera->nombre,
            'grupos'  => $grupos
        ]);
    }

    // GET /api/carreras/{id}/semestres
    public function semestres($id)
    {
        $carrera = DB::table('carrera')->where('id_carrera', $id)->first();
        if (!$carrera) {
            return response()->json(['error' => 'Carrera no encontrada'], 404);
        }
        $semestres = DB::table('plan_estudio as pe')
            ->join('plan_materia as pm', 'pe.id_plan',   '=', 'pm.id_plan')
            ->join('grupo as g',         'g.id_materia', '=', 'pm.id_materia')
            ->where('pe.id_carrera', $id)
            ->where('pe.estatus', 1)
            ->where('g.estatus',  1)
            ->select(
                'pm.semestre as numero',
                DB::raw('COUNT(DISTINCT g.id_grupo) as gruposCount'),
                DB::raw('SUM((SELECT COUNT(*) FROM inscripcion i WHERE i.id_grupo = g.id_grupo AND i.estatus = "Activo")) as total_inscritos')
            )
            ->groupBy('pm.semestre')
            ->orderBy('pm.semestre')
            ->get();
        return response()->json(['carrera' => $carrera->nombre, 'semestres' => $semestres]);
    }

    // GET /api/carreras/{id}/semestres/{semestre}/grupos
    public function gruposPorSemestre($id, $semestre)
    {
        $carrera = DB::table('carrera')->where('id_carrera', $id)->first();
        if (!$carrera) {
            return response()->json(['error' => 'Carrera no encontrada'], 404);
        }
        $materiasDelSemestre = DB::table('plan_estudio as pe')
            ->join('plan_materia as pm', 'pe.id_plan', '=', 'pm.id_plan')
            ->where('pe.id_carrera', $id)
            ->where('pe.estatus',    1)
            ->where('pm.semestre',   $semestre)
            ->pluck('pm.id_materia');
        if ($materiasDelSemestre->isEmpty()) {
            return response()->json(['carrera' => $carrera->nombre, 'semestre' => (int) $semestre, 'grupos' => []]);
        }
        $grupos = DB::table('grupo as g')
            ->join('materia as m',      'g.id_materia',  '=', 'm.id_materia')
            ->leftJoin('docente as d',  'g.id_docente',  '=', 'd.id_docente')
            ->leftJoin('empleado as e', 'd.id_empleado', '=', 'e.id_empleado')
            ->leftJoin('persona as p',  'e.id_persona',  '=', 'p.id_persona')
            ->whereIn('g.id_materia', $materiasDelSemestre)
            ->where('g.estatus', 1)
            ->select(
                'g.id_grupo as id',
                'g.clave_grupo as nombre',
                DB::raw("COALESCE(CONCAT(p.nombre,' ',p.apellido_paterno,' ',COALESCE(p.apellido_materno,'')), 'Sin asignar') as tutor"),
                DB::raw("(SELECT COUNT(*) FROM inscripcion i WHERE i.id_grupo = g.id_grupo AND i.estatus = 'Activo') as inscritos"),
                DB::raw("ROUND((SELECT AVG(sub.cal_pond) FROM (SELECT SUM(c.calificacion * ev.porcentaje / 100) as cal_pond FROM inscripcion i JOIN calificacion c ON c.id_inscripcion = i.id_inscripcion JOIN evaluacion ev ON ev.id_evaluacion = c.id_evaluacion WHERE i.id_grupo = g.id_grupo GROUP BY i.id_inscripcion) sub), 1) as promedio")
            )
            ->orderBy('g.clave_grupo')
            ->get()
            ->map(fn($g) => [
                'id'       => $g->id,
                'nombre'   => $g->nombre,
                'tutor'    => trim($g->tutor),
                'inscritos'=> (int) $g->inscritos,
                'promedio' => $g->promedio !== null ? (float) $g->promedio : null,
            ]);
        return response()->json(['carrera' => $carrera->nombre, 'semestre' => (int) $semestre, 'grupos' => $grupos->values()]);
    }

}

