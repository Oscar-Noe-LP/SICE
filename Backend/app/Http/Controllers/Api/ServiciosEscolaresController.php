<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Evaluacion;
use App\Models\Calificacion;
use App\Http\Controllers\Api\AlumnoController;
use App\Http\Controllers\Api\InscripcionController;

class ServiciosEscolaresController extends Controller
{
    // 🔹 CALIFICACIONES


    public function getCalificacionesGrupo(Request $request)
{
    $grupoId = $request->query('grupo_id');

    // JOIN evaluacion directo al grupo para tener los IDs aunque no haya calificaciones aún
    $datos = DB::table('inscripcion as i')
        ->join('alumno as a', 'i.id_alumno', '=', 'a.id_alumno')
        ->join('persona as p', 'a.id_persona', '=', 'p.id_persona')
        ->join('evaluacion as e', 'e.id_grupo', '=', 'i.id_grupo')
        ->leftJoin('calificacion as c', function ($join) {
            $join->on('c.id_inscripcion', '=', 'i.id_inscripcion')
                 ->on('c.id_evaluacion',  '=', 'e.id_evaluacion');
        })
        ->when($grupoId, fn($q) => $q->where('i.id_grupo', $grupoId))
        ->select(
            'i.id_inscripcion',
            'a.numero_control',
            DB::raw("CONCAT(p.nombre, ' ', p.apellido_paterno, ' ', p.apellido_materno) as nombre"),
            'e.id_evaluacion',
            'e.nombre as evaluacion',
            'c.calificacion'
        )
        ->get();

    $resultado = [];

    foreach ($datos as $d) {
        $key = $d->numero_control;

        if (!isset($resultado[$key])) {
            $resultado[$key] = [
                'id_inscripcion'          => $d->id_inscripcion,
                'control'                 => $d->numero_control,
                'nombre'                  => $d->nombre,
                'p1'                      => null,
                'p2'                      => null,
                'proy'                    => null,
                'id_evaluacion_parcial_1' => null,
                'id_evaluacion_parcial_2' => null,
                'id_evaluacion_proyecto'  => null,
            ];
        }

        $nombre = strtolower(trim($d->evaluacion ?? ''));

        if ($nombre === 'parcial 1') {
            $resultado[$key]['p1']                      = $d->calificacion;
            $resultado[$key]['id_evaluacion_parcial_1'] = $d->id_evaluacion;
        } elseif (str_contains($nombre, 'parcial 2') || str_contains($nombre, 'parcial2')) {
            $resultado[$key]['p2']                      = $d->calificacion;
            $resultado[$key]['id_evaluacion_parcial_2'] = $d->id_evaluacion;
        } elseif (str_contains($nombre, 'proyecto') || str_contains($nombre, 'project')) {
            // Solo sobreescribe si trae calificacion real o aún no tiene ID asignado
            if ($d->calificacion !== null || $resultado[$key]['id_evaluacion_proyecto'] === null) {
                $resultado[$key]['proy']                   = $d->calificacion;
                $resultado[$key]['id_evaluacion_proyecto'] = $d->id_evaluacion;
            }
        }
    }

    return response()->json(array_values($resultado));
}

    public function getCalificaciones(Request $request)
    {
        $grupoId   = $request->query('grupo')   ?? $request->query('grupo_id');
        $materiaId = $request->query('materia') ?? $request->query('materia_id');

        $query = DB::table('inscripcion as i')
            ->join('alumno as a', 'i.id_alumno', '=', 'a.id_alumno')
            ->join('persona as p', 'a.id_persona', '=', 'p.id_persona')
            ->join('grupo as g', 'i.id_grupo', '=', 'g.id_grupo')
            ->join('evaluacion as e', 'e.id_grupo', '=', 'i.id_grupo')
            ->leftJoin('calificacion as c', function ($join) {
                $join->on('c.id_inscripcion', '=', 'i.id_inscripcion')
                     ->on('c.id_evaluacion',  '=', 'e.id_evaluacion');
            })
            ->select(
                'i.id_inscripcion',
                'a.numero_control',
                DB::raw("CONCAT(p.nombre, ' ', p.apellido_paterno, ' ', p.apellido_materno) as nombre"),
                'e.id_evaluacion',
                'e.nombre as evaluacion',
                'c.calificacion'
            );

        if ($grupoId)   $query->where('i.id_grupo', $grupoId);
        if ($materiaId) $query->where('g.id_materia', $materiaId);

        $datos     = $query->get();
        $resultado = [];

        foreach ($datos as $d) {
            $key = $d->numero_control;
            if (!isset($resultado[$key])) {
                $resultado[$key] = [
                    'id_inscripcion'          => $d->id_inscripcion,
                    'control'                 => $d->numero_control,
                    'nombre'                  => $d->nombre,
                    'p1' => null, 'p2' => null, 'proy' => null,
                    'u1' => null, 'u2' => null, 'u3'  => null, 'u4' => null, 'u5' => null,
                    'id_evaluacion_parcial_1' => null,
                    'id_evaluacion_parcial_2' => null,
                    'id_evaluacion_proyecto'  => null,
                    'id_evaluacion_u1' => null, 'id_evaluacion_u2' => null,
                    'id_evaluacion_u3' => null, 'id_evaluacion_u4' => null,
                    'id_evaluacion_u5' => null,
                ];
            }
            $nombre = strtolower(trim($d->evaluacion ?? ''));
            if ($nombre === 'parcial 1')                                    { $resultado[$key]['p1']   = $d->calificacion; $resultado[$key]['id_evaluacion_parcial_1'] = $d->id_evaluacion; }
            elseif (str_contains($nombre, 'parcial 2'))                     { $resultado[$key]['p2']   = $d->calificacion; $resultado[$key]['id_evaluacion_parcial_2'] = $d->id_evaluacion; }
            elseif (str_contains($nombre, 'proyecto'))                      { $resultado[$key]['proy'] = $d->calificacion; $resultado[$key]['id_evaluacion_proyecto']  = $d->id_evaluacion; }
            elseif (str_contains($nombre, 'unidad 1') || $nombre === 'u1') { $resultado[$key]['u1']   = $d->calificacion; $resultado[$key]['id_evaluacion_u1'] = $d->id_evaluacion; }
            elseif (str_contains($nombre, 'unidad 2') || $nombre === 'u2') { $resultado[$key]['u2']   = $d->calificacion; $resultado[$key]['id_evaluacion_u2'] = $d->id_evaluacion; }
            elseif (str_contains($nombre, 'unidad 3') || $nombre === 'u3') { $resultado[$key]['u3']   = $d->calificacion; $resultado[$key]['id_evaluacion_u3'] = $d->id_evaluacion; }
            elseif (str_contains($nombre, 'unidad 4') || $nombre === 'u4') { $resultado[$key]['u4']   = $d->calificacion; $resultado[$key]['id_evaluacion_u4'] = $d->id_evaluacion; }
            elseif (str_contains($nombre, 'unidad 5') || $nombre === 'u5') { $resultado[$key]['u5']   = $d->calificacion; $resultado[$key]['id_evaluacion_u5'] = $d->id_evaluacion; }
        }

        return response()->json(array_values($resultado));
    }

    public function guardarCalificacionesUnidad(Request $request)
    {
        $request->validate([
            'materia_id'                      => 'required|integer',
            'unidad'                          => 'required|integer|min:1|max:5',
            'calificaciones'                  => 'required|array|min:1',
            'calificaciones.*.alumno_id'      => 'required|integer',
            'calificaciones.*.calificacion'   => 'required|numeric|min:0|max:100',
        ]);

        $nombreUnidad = 'Unidad ' . $request->unidad;
        $guardadas    = [];
        $errores      = [];

        foreach ($request->calificaciones as $item) {
            $inscripcion = DB::table('inscripcion as i')
                ->join('grupo as g', 'i.id_grupo', '=', 'g.id_grupo')
                ->where('i.id_alumno', $item['alumno_id'])
                ->where('g.id_materia', $request->materia_id)
                ->select('i.id_inscripcion', 'i.id_grupo')
                ->first();

            if (!$inscripcion) {
                $errores[] = "Alumno {$item['alumno_id']} no inscrito en materia {$request->materia_id}";
                continue;
            }

            $evaluacion = DB::table('evaluacion')
                ->where('id_grupo', $inscripcion->id_grupo)
                ->whereRaw('LOWER(nombre) LIKE ?', ['%' . strtolower($nombreUnidad) . '%'])
                ->first();

            if (!$evaluacion) {
                $errores[] = "No existe evaluacion '{$nombreUnidad}' en grupo {$inscripcion->id_grupo}";
                continue;
            }

            $cal = Calificacion::updateOrCreate(
                ['id_inscripcion' => $inscripcion->id_inscripcion, 'id_evaluacion' => $evaluacion->id_evaluacion],
                ['calificacion'   => $item['calificacion']]
            );

            BitacoraService::registrar('INSERT', 'calificacion', $cal->id_calificacion ?? 0, [], ['calificacion' => $item['calificacion']]);
            $guardadas[] = $cal;
        }

        $total = count($request->calificaciones);
        return response()->json([
            'guardadas' => count($guardadas),
            'errores'   => $errores,
        ], count($errores) === $total ? 422 : 200);
    }

    public function guardarCalificaciones(Request $request)
{
    $request->validate([
        'id_inscripcion' => 'required|integer',
        'id_evaluacion'  => 'required|integer',
        'calificacion'   => 'required|numeric|min:0|max:100',
        'fecha_registro' => 'nullable|date'
    ]);

    // Verificar que el acta del grupo no esté cerrada
    $actaCerrada = DB::table('inscripcion as i')
        ->join('grupo as g', 'i.id_grupo', '=', 'g.id_grupo')
        ->where('i.id_inscripcion', $request->id_inscripcion)
        ->value('g.acta_cerrada');

    if ($actaCerrada) {
        return response()->json(['mensaje' => 'No se puede modificar: el acta está cerrada'], 403);
    }

    $anterior = Calificacion::where('id_inscripcion', $request->id_inscripcion)
        ->where('id_evaluacion', $request->id_evaluacion)
        ->first();

    $calificacion = Calificacion::updateOrCreate(
        [
            'id_inscripcion' => $request->id_inscripcion,
            'id_evaluacion'  => $request->id_evaluacion,
        ],
        [
            'calificacion'   => $request->calificacion,
        ]
    );

    BitacoraService::registrar(
        $anterior ? 'UPDATE' : 'INSERT',
        'calificacion',
        $calificacion->id_calificacion ?? $request->id_inscripcion,
        $anterior ? ['calificacion' => $anterior->calificacion] : [],
        ['calificacion' => $request->calificacion]
    );

    return response()->json([
        'mensaje'       => 'Calificación guardada correctamente',
        'calificacion'  => $calificacion
    ], 200);
}

    public function actualizarCalificacion(Request $request, $id)
    {
        $request->validate([
            'calificacion' => 'required|numeric|min:0|max:100'
        ]);

        $calificacion = Calificacion::findOrFail($id);

        // Verificar que el acta no esté cerrada
        $actaCerrada = DB::table('inscripcion as i')
            ->join('grupo as g', 'i.id_grupo', '=', 'g.id_grupo')
            ->where('i.id_inscripcion', $calificacion->id_inscripcion)
            ->value('g.acta_cerrada');

        if ($actaCerrada) {
            return response()->json(['mensaje' => 'No se puede modificar: el acta está cerrada'], 403);
        }

        $anterior = $calificacion->calificacion;
        $calificacion->update($request->all());

        BitacoraService::registrar('UPDATE', 'calificacion', $id,
            ['calificacion' => $anterior],
            ['calificacion' => $request->calificacion]
        );

        return response()->json([
            'mensaje' => 'Calificación actualizada',
            'calificacion' => $calificacion
        ], 200);
    }

    public function eliminarCalificacion($id)
    {
        $calificacion = Calificacion::find($id);

        if ($calificacion) {
            // Verificar acta cerrada
            $actaCerrada = DB::table('inscripcion as i')
                ->join('grupo as g', 'i.id_grupo', '=', 'g.id_grupo')
                ->where('i.id_inscripcion', $calificacion->id_inscripcion)
                ->value('g.acta_cerrada');

            if ($actaCerrada) {
                return response()->json(['mensaje' => 'No se puede eliminar: el acta está cerrada'], 403);
            }

            BitacoraService::registrar('DELETE', 'calificacion', $id,
                ['calificacion' => $calificacion->calificacion]
            );
        }

        Calificacion::destroy($id);
        return response()->json(['mensaje' => 'Calificación eliminada'], 200);
    }

    // 🔹 ALUMNOS
    public function getAlumnos()
    {
        $alumnos = DB::table('alumno as a')
            ->join('persona as p', 'a.id_persona', '=', 'p.id_persona')
            ->join('carrera as c', 'a.id_carrera', '=', 'c.id_carrera')
            ->select(
                'a.id_alumno',
                'a.numero_control',
                'a.id_carrera',
                'a.semestre_actual',
                'a.estatus',
                DB::raw("CONCAT(p.nombre, ' ', p.apellido_paterno, ' ', p.apellido_materno) as nombre"),
                'c.nombre as carrera'
            )
            ->get()
            ->map(function ($alumno) {
                return [
                    'id_alumno' => $alumno->id_alumno,
                    'numero_control' => $alumno->numero_control,
                    'id_carrera' => $alumno->id_carrera,
                    'semestre_actual' => $alumno->semestre_actual,
                    'estatus' => $alumno->estatus,  // VARCHAR: 'Activo', 'Baja Temporal', etc.
                    'nombre' => $alumno->nombre,
                    'carrera' => [
                        'nombre_carrera' => $alumno->carrera // 👈 importante para Vue
                    ]
                ];
            });

        return response()->json($alumnos);
    }

    public function store(Request $request)
    {
        return app(AlumnoController::class)->store($request);
    }

    public function buscarAlumnoInscripcion(Request $request)
    {
        $termino = trim($request->query('q', $request->query('termino', '')));

        if (strlen($termino) < 2) {
            return response()->json(['error' => 'El término de búsqueda debe tener al menos 2 caracteres'], 422);
        }

        $terminoUpper = strtoupper($termino);

        $alumnos = DB::table('alumno as a')
            ->join('persona as p', 'a.id_persona', '=', 'p.id_persona')
            ->join('carrera as c', 'a.id_carrera', '=', 'c.id_carrera')
            ->where(function ($q) use ($terminoUpper) {
                $q->whereRaw('UPPER(a.numero_control) LIKE ?', ["%{$terminoUpper}%"])
                ->orWhereRaw(
                    "UPPER(CONCAT(p.nombre, ' ', p.apellido_paterno, ' ', p.apellido_materno)) LIKE ?",
                    ["%{$terminoUpper}%"]
                )
                ->orWhereRaw(
                    "UPPER(CONCAT(p.apellido_paterno, ' ', p.apellido_materno, ' ', p.nombre)) LIKE ?",
                    ["%{$terminoUpper}%"]
                );
            })
            ->select(
                'a.id_alumno',
                'a.id_persona',              // ← necesario para deduplicar en frontend
                'a.numero_control',
                'a.semestre_actual',
                'a.estatus',
                DB::raw("UPPER(CONCAT(p.apellido_paterno, ' ', p.apellido_materno, ' ', p.nombre)) as nombre_completo"),
                'c.nombre as carrera'
            )
            ->limit(20)
            ->get();

        return response()->json($alumnos);
    }

    // 🔹 GRUPOS / INSCRIPCIÓN
    public function getGruposDisponibles()
    {
        return app(InscripcionController::class)->gruposDisponibles();
    }

    public function inscribirAlumno(Request $request)
    {
        return app(InscripcionController::class)->inscribirAlumno($request);
    }

    // 🔹 EVALUACIONES
    public function getEvaluaciones($id_grupo)
    {
        $evaluaciones = DB::table('evaluacion')
            ->where('id_grupo', $id_grupo)
            ->get();

        return response()->json($evaluaciones);
    }

    public function guardarEvaluaciones(Request $request)
    {
        $request->validate([
            'id_grupo' => 'required|integer',
            'nombre' => 'required|string|max:50',
            'porcentaje' => 'required|numeric|min:0|max:100'
        ]);

        $evaluacion = Evaluacion::create($request->all());

        return response()->json([
            'mensaje' => 'Evaluación guardada correctamente',
            'evaluacion' => $evaluacion
        ], 201);
    }

    public function actualizarEvaluacion(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:50',
            'porcentaje' => 'required|numeric|min:0|max:100'
        ]);

        $evaluacion = Evaluacion::findOrFail($id);
        $evaluacion->update($request->all());

        return response()->json([
            'mensaje' => 'Evaluación actualizada',
            'evaluacion' => $evaluacion
        ], 200);
    }

    public function eliminarEvaluacion($id)
    {
        // Primero borrar las calificaciones asociadas
        DB::table('calificacion')->where('id_evaluacion', $id)->delete();

        // Luego borrar la evaluación
        Evaluacion::destroy($id);

        return response()->json(['mensaje' => 'Evaluación eliminada'], 200);
    }

    // 🔹 ACTAS

    public function getActasPorCarrera($id_carrera)
    {
        try {
            $periodo = DB::table('periodo')->where('estatus', 1)->orderByDesc('id_periodo')->first();

            $actas = DB::table('grupo as g')
                ->join('materia as m', 'g.id_materia', '=', 'm.id_materia')
                ->join('inscripcion as i', 'g.id_grupo', '=', 'i.id_grupo')
                ->join('alumno as a', 'i.id_alumno', '=', 'a.id_alumno')
                ->leftJoin('persona as p', function ($join) {
                    $join->on('p.id_persona', '=',
                        DB::raw('(SELECT id_persona FROM usuario WHERE id_usuario = g.id_docente LIMIT 1)')
                    );
                })
                ->leftJoin('calificacion as c', 'c.id_inscripcion', '=', 'i.id_inscripcion')
                ->where('a.id_carrera', $id_carrera)
                ->when($periodo, fn($q) => $q->where('g.id_periodo', $periodo->id_periodo))
                ->select(
                    'g.id_grupo',
                    'g.clave_grupo',
                    'm.nombre as materia',
                    'm.clave as clave_materia',
                    'g.estatus',
                    DB::raw('COUNT(DISTINCT i.id_alumno) as inscritos'),
                    DB::raw('COUNT(DISTINCT c.id_calificacion) as calificaciones_registradas')
                )
                ->groupBy('g.id_grupo', 'g.clave_grupo', 'm.nombre', 'm.clave', 'g.estatus')
                ->get()
                ->map(fn($g) => [
                    'id_acta'                  => $g->id_grupo,
                    'id_grupo'                 => $g->id_grupo,
                    'clave_grupo'              => $g->clave_grupo,
                    'materia'                  => $g->materia,
                    'clave_materia'            => $g->clave_materia,
                    'inscritos'                => (int) $g->inscritos,
                    'calificaciones_registradas' => (int) $g->calificaciones_registradas,
                    'cerrada'                  => $g->estatus == 0,
                    'estatus'                  => $g->estatus,
                ]);

            return response()->json($actas);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function generarActa($id_grupo)
    {
        try {
            $grupo = DB::table('grupo')->where('id_grupo', $id_grupo)->first();

            if (!$grupo) {
                return response()->json(['error' => 'Grupo no encontrado'], 404);
            }

            $calificaciones = DB::table('inscripcion as i')
                ->join('alumno as a', 'i.id_alumno', '=', 'a.id_alumno')
                ->join('persona as p', 'a.id_persona', '=', 'p.id_persona')
                ->leftJoin('calificacion as c', 'c.id_inscripcion', '=', 'i.id_inscripcion')
                ->leftJoin('evaluacion as e', 'e.id_evaluacion', '=', 'c.id_evaluacion')
                ->where('i.id_grupo', $id_grupo)
                ->select(
                    'a.numero_control',
                    DB::raw("CONCAT(p.nombre,' ',p.apellido_paterno,' ',p.apellido_materno) as nombre"),
                    'e.nombre as evaluacion',
                    'c.calificacion'
                )
                ->get();

            DB::table('grupo')->where('id_grupo', $id_grupo)->update(['estatus' => 0]);

            BitacoraService::registrar('UPDATE', 'grupo', $id_grupo, ['estatus' => 1], ['estatus' => 0]);

            return response()->json([
                'mensaje'        => 'Acta generada correctamente',
                'id_grupo'       => (int) $id_grupo,
                'calificaciones' => $calificaciones,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 🔹 HISTORIAL ALUMNO

    public function historialPorControl($numero_control)
    {
        try {
            $alumno = DB::table('alumno as a')
                ->join('persona as p', 'a.id_persona', '=', 'p.id_persona')
                ->where('a.numero_control', $numero_control)
                ->select('a.id_alumno', 'a.numero_control', 'a.semestre_actual',
                    DB::raw("CONCAT(p.nombre,' ',p.apellido_paterno,' ',p.apellido_materno) as nombre"),
                    'a.id_carrera')
                ->first();

            if (!$alumno) {
                return response()->json(['error' => 'Alumno no encontrado'], 404);
            }

            $datos = DB::table('inscripcion as i')
                ->join('grupo as g', 'i.id_grupo', '=', 'g.id_grupo')
                ->join('materia as m', 'g.id_materia', '=', 'm.id_materia')
                ->join('periodo as per', 'g.id_periodo', '=', 'per.id_periodo')
                ->join('evaluacion as e', 'e.id_grupo', '=', 'g.id_grupo')
                ->leftJoin('calificacion as c', function ($join) {
                    $join->on('c.id_inscripcion', '=', 'i.id_inscripcion')
                         ->on('c.id_evaluacion',  '=', 'e.id_evaluacion');
                })
                ->where('i.id_alumno', $alumno->id_alumno)
                ->select(
                    'per.id_periodo',
                    'per.nombre_periodo as periodo',
                    'm.id_materia',
                    'm.nombre as materia',
                    'm.clave as clave_materia',
                    'g.clave_grupo',
                    'e.nombre as evaluacion',
                    'c.calificacion'
                )
                ->orderBy('per.id_periodo')
                ->get();

            $historial = [];
            foreach ($datos as $d) {
                $pKey = $d->id_periodo;
                $mKey = $d->id_materia;

                if (!isset($historial[$pKey])) {
                    $historial[$pKey] = ['periodo' => $d->periodo, 'materias' => []];
                }
                if (!isset($historial[$pKey]['materias'][$mKey])) {
                    $historial[$pKey]['materias'][$mKey] = [
                        'materia'      => $d->materia,
                        'clave'        => $d->clave_materia,
                        'grupo'        => $d->clave_grupo,
                        'calificaciones' => [],
                    ];
                }
                if ($d->evaluacion) {
                    $historial[$pKey]['materias'][$mKey]['calificaciones'][] = [
                        'evaluacion'   => $d->evaluacion,
                        'calificacion' => $d->calificacion,
                    ];
                }
            }

            $resultado = array_values(array_map(function ($p) {
                $p['materias'] = array_values($p['materias']);
                return $p;
            }, $historial));

            return response()->json([
                'alumno'   => $alumno,
                'historial' => $resultado,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 🔹 RESUMEN ESCOLAR
    public function getResumen()
    {
        try {
            return response()->json([
                'total_alumnos'       => DB::table('alumno')->count(),
                'alumnos_activos'     => DB::table('alumno')->where('estatus', 'Activo')->count(),
                'total_grupos'        => DB::table('grupo')->count(),
                'total_inscripciones' => DB::table('inscripcion')->count(),
                'total_materias'      => DB::table('materia')->count(),
                'total_evaluaciones'  => DB::table('evaluacion')->count(),
                'total_calificaciones'=> DB::table('calificacion')->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
