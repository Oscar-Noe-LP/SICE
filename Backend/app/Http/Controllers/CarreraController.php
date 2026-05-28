<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CarreraController extends Controller
{
    // ─────────────────────────────────────────────
    // Obtener todas las carreras
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
                'n.nombre_nivel'
            )
            ->get()
            ->map(function ($c) {
                return [
                    'id_carrera' => $c->id_carrera,
                    'nombre' => $c->nombre,
                    'id_departamento' => $c->id_departamento,
                    'id_nivel' => $c->id_nivel,
                    'estatus' => $c->estatus,
                    'departamento' => [
                        'nombre' => $c->departamento_nombre
                    ],
                    'nivel' => [
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
    // Drill-down: grupos que pertenecen a una carrera
    //
    // La cadena de relación es:
    //   carrera → plan_estudio → plan_materia → materia → grupo
    //
    // No existe id_carrera directo en grupo; la carrera define
    // qué materias se imparten a través de su plan de estudios.
    //
    // GET /api/carreras/{id}/grupos
    public function grupos($id)
    {
        // 1. Verificar que la carrera existe y obtener su nombre
        $carrera = DB::table('carrera')
            ->where('id_carrera', $id)
            ->select('id_carrera', 'nombre')
            ->first();

        if (!$carrera) {
            return response()->json(['message' => 'Carrera no encontrada'], 404);
        }

        // 2. Obtener los id_materia del plan de estudios activo de esta carrera
        $idMaterias = DB::table('plan_estudio as pe')
            ->join('plan_materia as pm', 'pe.id_plan', '=', 'pm.id_plan')
            ->where('pe.id_carrera', $id)
            ->where('pe.estatus', 1)
            ->pluck('pm.id_materia');

        if ($idMaterias->isEmpty()) {
            return response()->json([
                'carrera' => $carrera->nombre,
                'grupos'  => []
            ]);
        }

        // 3. Traer grupos cuya materia pertenezca a esa carrera
        $grupos = DB::table('grupo as g')
            ->join('materia as m', 'g.id_materia', '=', 'm.id_materia')
            ->join('docente as d', 'g.id_docente', '=', 'd.id_docente')
            ->join('empleado as e', 'd.id_empleado', '=', 'e.id_empleado')
            ->join('persona as p', 'e.id_persona', '=', 'p.id_persona')
            ->leftJoin('turno as t', 'g.id_turno', '=', 't.id_turno')
            ->whereIn('g.id_materia', $idMaterias)
            ->where('g.estatus', 1)
            ->select(
                'g.id_grupo',
                'g.clave_grupo',
                'm.nombre as materia',
                DB::raw("UPPER(CONCAT(p.apellido_paterno, ' ', p.apellido_materno, ' ', p.nombre)) as docente"),
                't.nombre_turno as turno',
                'g.dia',
                DB::raw("TIME_FORMAT(g.hora_inicio, '%H:%i') as hora_inicio"),
                DB::raw("TIME_FORMAT(g.hora_fin, '%H:%i') as hora_fin"),
                'g.capacidad',
                DB::raw("(
                    SELECT COUNT(*)
                    FROM inscripcion AS i
                    WHERE i.id_grupo = g.id_grupo
                      AND i.estatus = 'Activo'
                ) as inscritos")
            )
            ->orderBy('m.nombre')
            ->orderBy('g.dia')
            ->orderBy('g.hora_inicio')
            ->get();

        return response()->json([
            'carrera' => $carrera->nombre,
            'grupos'  => $grupos
        ]);
    }
}
