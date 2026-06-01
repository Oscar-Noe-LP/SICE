<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GrupoController extends Controller
{
    public function index()
    {
        try {
            $grupos = DB::table('grupo as g')
                ->leftJoin('materia as m', 'g.id_materia', '=', 'm.id_materia')
                ->leftJoin('docente as d', 'g.id_docente', '=', 'd.id_docente')
                ->leftJoin('empleado as e', 'd.id_empleado', '=', 'e.id_empleado')
                ->leftJoin('persona as p', 'e.id_persona', '=', 'p.id_persona')
                ->leftJoin('aula as a', 'g.id_aula', '=', 'a.id_aula')
                ->leftJoin('inscripcion as i', 'g.id_grupo', '=', 'i.id_grupo')

                ->select(
                    'g.id_grupo',
                    'g.clave_grupo',

                    'm.nombre as materia',
                    'm.id_materia',

                    DB::raw("
                        COALESCE(
                            CONCAT(
                                p.nombre,
                                ' ',
                                p.apellido_paterno,
                                ' ',
                                COALESCE(p.apellido_materno, '')
                            ),
                            'Sin docente'
                        ) as docente
                    "),

                    'a.nombre as aula',

                    'g.capacidad',
                    'g.id_periodo',

                    DB::raw("
                        COUNT(
                            CASE
                                WHEN i.estatus IN ('Activo','activo','inscrito')
                                THEN 1
                            END
                        ) as inscritos
                    ")
                )

                ->groupBy(
                    'g.id_grupo',
                    'g.clave_grupo',
                    'm.nombre',
                    'm.id_materia',
                    'p.nombre',
                    'p.apellido_paterno',
                    'p.apellido_materno',
                    'a.nombre',
                    'g.capacidad',
                    'g.id_periodo'
                )

                ->get()

                ->map(function ($g) {

                    return [
                        'id' => $g->id_grupo,

                        'id_grupo' => $g->id_grupo,

                        'clave_grupo' => $g->clave_grupo,

                        'materia' => $g->materia,

                        'docente' => trim($g->docente),

                        'aula' => $g->aula,

                        'capacidad' => (int) ($g->capacidad ?? 0),

                        'inscritos' => (int) ($g->inscritos ?? 0),

                        // Tu frontend espera estas propiedades
                        'id_carrera' => null,
                        'semestre' => 0,

                        // Tu frontend espera horario
                        'horario' => [
                            'dia' => '',
                            'horaInicio' => '',
                            'horaFin' => '',
                        ],

                        // Para filtros por alumno
                        'alumnos' => [],
                    ];
                });

            return response()->json($grupos);
        } catch (\Exception $e) {

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [

                'clave_grupo' => 'nullable|string|max:20|unique:grupo,clave_grupo',

                'nombre_materia' => 'required|string',

                'aula' => 'required|string',

                'nombre_docente' => 'nullable|string',

                'capacidad' => 'required|integer|min:1',

                'id_periodo' => 'nullable|integer|exists:periodo,id_periodo',

            ], [

                'clave_grupo.unique' => 'Ya existe un grupo con esa clave',

                'nombre_materia.required' => 'La materia es requerida',

                'aula.required' => 'El aula es requerida',

                'capacidad.required' => 'La capacidad es requerida',

            ]);

            if ($validator->fails()) {

                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // MATERIA

            $id_materia = DB::table('materia')
                ->where('nombre', $request->nombre_materia)
                ->value('id_materia');

            if (!$id_materia) {

                return response()->json([
                    'success' => false,
                    'error' => 'La materia indicada no existe'
                ], 422);
            }

            // AULA

            $id_aula = DB::table('aula')
                ->where('nombre', $request->aula)
                ->value('id_aula');

            if (!$id_aula) {

                return response()->json([
                    'success' => false,
                    'error' => 'El aula indicada no existe'
                ], 422);
            }

            // DOCENTE

            $id_docente = null;

            if ($request->filled('nombre_docente')) {

                $id_docente = DB::table('docente as d')

                    ->join(
                        'empleado as e',
                        'd.id_empleado',
                        '=',
                        'e.id_empleado'
                    )

                    ->join(
                        'persona as p',
                        'e.id_persona',
                        '=',
                        'p.id_persona'
                    )

                    ->where(
                        DB::raw("
                            TRIM(
                                CONCAT(
                                    p.nombre,
                                    ' ',
                                    p.apellido_paterno,
                                    ' ',
                                    COALESCE(p.apellido_materno, '')
                                )
                            )
                        "),
                        'like',
                        $request->nombre_docente . '%'
                    )

                    ->value('d.id_docente');

                if (!$id_docente) {

                    return response()->json([
                        'success' => false,
                        'error' => 'El docente indicado no existe'
                    ], 422);
                }
            }
            // PERIODO

            $id_periodo = $request->id_periodo
                ?? DB::table('periodo')
                ->where('estatus', 1)
                ->orderByDesc('id_periodo')
                ->value('id_periodo');

            // CLAVE

            $clave_grupo = $request->clave_grupo
                ?? strtoupper(
                    substr(
                        preg_replace(
                            '/[^A-Za-z0-9]/',
                            '',
                            $request->nombre_materia
                        ),
                        0,
                        6
                    )
                ) . '-' . now()->format('mdHi');

            // INSERT

            $id = DB::table('grupo')->insertGetId([

                'clave_grupo' => $clave_grupo,

                'id_materia' => $id_materia,

                'id_docente' => $id_docente,

                'id_aula' => $id_aula,

                'id_periodo' => $id_periodo,

                'capacidad' => $request->capacidad,

                'estatus' => 1,
            ]);

            BitacoraService::registrar(
                'INSERT',
                'grupo',
                $id,
                [],
                [
                    'clave_grupo' => $clave_grupo,
                    'id_materia' => $id_materia,
                    'id_docente' => $id_docente,
                    'capacidad' => $request->capacidad,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Grupo creado',
                'id' => $id
            ], 201);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {

            $grupo = DB::table('grupo')
                ->where('id_grupo', $id)
                ->first();

            if (!$grupo) {

                return response()->json([
                    'success' => false,
                    'error' => 'Grupo no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [

                'clave_grupo' =>
                'sometimes|string|max:20|unique:grupo,clave_grupo,' .
                    $id .
                    ',id_grupo',

                'nombre_materia' => 'sometimes|string',

                'aula' => 'sometimes|string',

                'nombre_docente' => 'nullable|string',

                'capacidad' => 'sometimes|integer|min:1',

                'id_periodo' =>
                'sometimes|nullable|integer|exists:periodo,id_periodo',

            ]);

            if ($validator->fails()) {

                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // MATERIA

            $id_materia = $grupo->id_materia;

            if ($request->filled('nombre_materia')) {

                $id_materia = DB::table('materia')
                    ->where('nombre', $request->nombre_materia)
                    ->value('id_materia');

                if (!$id_materia) {

                    return response()->json([
                        'success' => false,
                        'error' => 'La materia indicada no existe'
                    ], 422);
                }
            }

            // AULA

            $id_aula = $grupo->id_aula;

            if ($request->filled('aula')) {

                $id_aula = DB::table('aula')
                    ->where('nombre', $request->aula)
                    ->value('id_aula');

                if (!$id_aula) {

                    return response()->json([
                        'success' => false,
                        'error' => 'El aula indicada no existe'
                    ], 422);
                }
            }

            // DOCENTE

            $id_docente = $grupo->id_docente;

            if ($request->has('nombre_docente')) {

                if ($request->filled('nombre_docente')) {

                    $id_docente = DB::table('docente as d')

                        ->join(
                            'empleado as e',
                            'd.id_empleado',
                            '=',
                            'e.id_empleado'
                        )

                        ->join(
                            'persona as p',
                            'e.id_persona',
                            '=',
                            'p.id_persona'
                        )

                        ->where(
                            DB::raw("
                                TRIM(
                                    CONCAT(
                                        p.nombre,
                                        ' ',
                                        p.apellido_paterno,
                                        ' ',
                                        COALESCE(p.apellido_materno, '')
                                    )
                                )
                            "),
                            'like',
                            $request->nombre_docente . '%'
                        )

                        ->value('d.id_docente');

                    if (!$id_docente) {

                        return response()->json([
                            'success' => false,
                            'error' => 'El docente indicado no existe'
                        ], 422);
                    }
                } else {

                    $id_docente = null;
                }
            }

            DB::table('grupo')
                ->where('id_grupo', $id)
                ->update([

                    'clave_grupo' =>
                    $request->clave_grupo ?? $grupo->clave_grupo,

                    'id_materia' => $id_materia,

                    'id_aula' => $id_aula,

                    'id_docente' => $id_docente,

                    'id_periodo' =>
                    $request->id_periodo ?? $grupo->id_periodo,

                    'capacidad' =>
                    $request->capacidad ?? $grupo->capacidad,
                ]);

            BitacoraService::registrar(
                'UPDATE',
                'grupo',
                $id,
                (array) $grupo,
                $request->all()
            );

            return response()->json([
                'success' => true,
                'message' => 'Grupo actualizado'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {

            $grupo = DB::table('grupo')
                ->where('id_grupo', $id)
                ->first();

            if (!$grupo) {

                return response()->json([
                    'success' => false,
                    'error' => 'Grupo no encontrado'
                ], 404);
            }

            $inscritos = DB::table('inscripcion')
                ->where('id_grupo', $id)
                ->whereIn('estatus', [
                    'Activo',
                    'activo',
                    'inscrito'
                ])
                ->count();

            if ($inscritos > 0) {

                return response()->json([
                    'success' => false,
                    'error' =>
                    'No se puede eliminar el grupo porque tiene alumnos inscritos'
                ], 409);
            }

            DB::table('grupo')
                ->where('id_grupo', $id)
                ->delete();

            BitacoraService::registrar(
                'DELETE',
                'grupo',
                $id,
                (array) $grupo
            );

            return response()->json([
                'success' => true,
                'message' => 'Grupo eliminado'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function cerrarActa(int $id)
    {
        return response()->json([
            'success' => false,
            'error' =>
            'Funcionalidad no disponible en esta versión de la BD'
        ], 503);
    }

    public function abrirActa(int $id)
    {
        return response()->json([
            'success' => false,
            'error' =>
            'Funcionalidad no disponible en esta versión de la BD'
        ], 503);
    }

    public function materias(int $id)
    {
        try {
            $grupo = DB::table('grupo as g')
                ->leftJoin('materia as m', 'g.id_materia', '=', 'm.id_materia')
                ->leftJoin('docente as d',   'g.id_docente',  '=', 'd.id_docente')
                ->leftJoin('empleado as e',  'd.id_empleado', '=', 'e.id_empleado')
                ->leftJoin('persona as p',   'e.id_persona',  '=', 'p.id_persona')
                ->where('g.id_grupo', $id)
                ->select(
                    'g.id_grupo',
                    'g.clave_grupo',
                    'm.id_materia',
                    'm.nombre as nombre_materia',
                    'm.clave as clave_materia',
                    'm.creditos',
                    DB::raw("COALESCE(
                        CONCAT(p.nombre, ' ', p.apellido_paterno, ' ', COALESCE(p.apellido_materno, '')),
                        'Sin docente'
                    ) as docente"),
                    DB::raw("(
                        SELECT COUNT(*)
                        FROM inscripcion AS i
                        WHERE i.id_grupo = g.id_grupo
                        AND i.estatus IN ('Activo', 'activo', 'inscrito')
                    ) as inscritos")
                )
                ->first();

            if (!$grupo) {
                return response()->json(['error' => 'Grupo no encontrado'], 404);
            }

            return response()->json([
                'id_grupo'    => $grupo->id_grupo,
                'clave_grupo' => $grupo->clave_grupo,
                'materias'    => [[
                    'id_materia' => $grupo->id_materia,
                    'nombre'     => $grupo->nombre_materia,
                    'clave'      => $grupo->clave_materia,
                    'creditos'   => $grupo->creditos,
                    'docente'    => trim($grupo->docente),
                    'inscritos'  => (int) $grupo->inscritos,
                ]],
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // =========================================================================

    /**
     * GET /api/grupos/{id}
     *
     * Detalle completo de un grupo para la vista de Grupos y Horarios.
     * Devuelve header, información, tutor, alumnos inscritos y su promedio.
     */
    public function show(int $id)
    {
        try {
            $grupo = DB::table('grupo as g')
                ->join('materia as m',       'g.id_materia',  '=', 'm.id_materia')
                ->leftJoin('docente as d',   'g.id_docente',  '=', 'd.id_docente')
                ->leftJoin('empleado as em', 'd.id_empleado', '=', 'em.id_empleado')
                ->leftJoin('persona as pd',  'em.id_persona', '=', 'pd.id_persona')
                ->leftJoin('aula as a',      'g.id_aula',     '=', 'a.id_aula')
                ->leftJoin('edificio as ed', 'a.id_edificio', '=', 'ed.id_edificio')
                ->leftJoin('periodo as per', 'g.id_periodo',  '=', 'per.id_periodo')
                ->leftJoin('plan_materia as pm', 'pm.id_materia', '=', 'm.id_materia')
                ->leftJoin('plan_estudio as pe', function ($j) {
                    $j->on('pe.id_plan', '=', 'pm.id_plan')->where('pe.estatus', 1);
                })
                ->leftJoin('carrera as c',   'pe.id_carrera', '=', 'c.id_carrera')
                ->where('g.id_grupo', $id)
                ->select(
                    'g.id_grupo',
                    'g.clave_grupo',
                    'g.capacidad',
                    'g.estatus',
                    'm.nombre as nombre_materia',
                    'pm.semestre',
                    'per.nombre_periodo as periodo',
                    'a.nombre as aula_nombre',
                    'ed.nombre_edificio as edificio_nombre',
                    'c.nombre as carrera_nombre',
                    'pe.nombre_plan as plan_estudios',
                    DB::raw("COALESCE(
                        CONCAT(pd.nombre,' ',pd.apellido_paterno,' ',COALESCE(pd.apellido_materno,'')),
                        'Sin asignar'
                    ) as docente_nombre"),
                    'pd.curp as tutor_curp',
                    DB::raw("(SELECT correo FROM persona_correo WHERE id_persona = pd.id_persona LIMIT 1) as tutor_correo"),
                    DB::raw("(SELECT telefono FROM persona_telefono WHERE id_persona = pd.id_persona LIMIT 1) as tutor_telefono"),
                    'd.grado_academico as tutor_grado',
                    // Horario desde horarios_grupos si existe, sino desde grupo
                    DB::raw("COALESCE(
                        (SELECT hg.hora_inicio FROM horarios_grupos hg WHERE hg.id_grupo = g.id_grupo LIMIT 1),
                        g.hora_inicio
                    ) as hora_inicio"),
                    DB::raw("COALESCE(
                        (SELECT hg.hora_fin FROM horarios_grupos hg WHERE hg.id_grupo = g.id_grupo LIMIT 1),
                        g.hora_fin
                    ) as hora_fin"),
                    DB::raw("COALESCE(
                        (SELECT hg.dia FROM horarios_grupos hg WHERE hg.id_grupo = g.id_grupo LIMIT 1),
                        g.dia
                    ) as dia_clase")
                )
                ->first();

            if (!$grupo) {
                return response()->json(['error' => 'Grupo no encontrado'], 404);
            }

            // Conteo de inscritos
            $inscritos = DB::table('inscripcion')
                ->where('id_grupo', $id)
                ->whereIn('estatus', ['Activo', 'activo', 'inscrito'])
                ->count();

            // Regulares: promedio >= 70 en todas sus materias
            // Irregulares: al menos una materia reprobada
            $promediosPorAlumno = DB::table('inscripcion as i')
                ->join('calificacion as c',  'c.id_inscripcion', '=', 'i.id_inscripcion')
                ->join('evaluacion as ev',   'c.id_evaluacion',  '=', 'ev.id_evaluacion')
                ->where('i.id_grupo', $id)
                ->select(
                    'i.id_alumno',
                    DB::raw('SUM(c.calificacion * ev.porcentaje / 100) as promedio')
                )
                ->groupBy('i.id_alumno')
                ->get();

            $regulares   = $promediosPorAlumno->where('promedio', '>=', 70)->count();
            $irregulares = $promediosPorAlumno->where('promedio', '<',  70)->count();

            // Alumnos del grupo con promedio
            $alumnos = DB::table('inscripcion as i')
                ->join('alumno as a',  'i.id_alumno', '=', 'a.id_alumno')
                ->join('persona as p', 'a.id_persona', '=', 'p.id_persona')
                ->leftJoin('estatus_alumno as ea', 'a.id_estatus_alumno', '=', 'ea.id_estatus_alumno')
                ->where('i.id_grupo', $id)
                ->whereIn('i.estatus', ['Activo', 'activo', 'inscrito'])
                ->select(
                    'a.numero_control as noControl',
                    DB::raw("CONCAT(p.nombre,' ',p.apellido_paterno,' ',COALESCE(p.apellido_materno,'')) as nombre"),
                    DB::raw("COALESCE(ea.nombre, a.estatus, 'Activo') as estatus"),
                    'i.id_inscripcion'
                )
                ->get()
                ->map(function ($alumno) {
                    $promedio = DB::table('calificacion as c')
                        ->join('evaluacion as ev', 'c.id_evaluacion', '=', 'ev.id_evaluacion')
                        ->where('c.id_inscripcion', $alumno->id_inscripcion)
                        ->sum(DB::raw('c.calificacion * ev.porcentaje / 100'));

                    return [
                        'noControl' => $alumno->noControl,
                        'nombre'    => trim($alumno->nombre),
                        'estatus'   => $alumno->estatus,
                        'promedio'  => $promedio ? round($promedio, 1) : null,
                    ];
                });

            return response()->json([
                // Header
                'nombre_grupo'   => $grupo->clave_grupo,
                'semestre'       => $grupo->semestre,
                'turno'          => null,
                'carrera_nombre' => $grupo->carrera_nombre,
                'inscritos'      => $inscritos,
                'regulares'      => $regulares,
                'irregulares'    => $irregulares,
                // Información
                'clave_grupo'    => $grupo->clave_grupo,
                'plan_estudios'  => $grupo->plan_estudios,
                'aula_nombre'    => $grupo->aula_nombre,
                'edificio_nombre'=> $grupo->edificio_nombre,
                'capacidad'      => $grupo->capacidad,
                'horario'        => $grupo->hora_inicio && $grupo->hora_fin
                    ? $grupo->hora_inicio . ' - ' . $grupo->hora_fin
                    : null,
                'dias_clase'     => $grupo->dia_clase,
                // Tutor
                'docente_nombre' => trim($grupo->docente_nombre),
                'tutor_correo'   => $grupo->tutor_correo,
                'tutor_telefono' => $grupo->tutor_telefono,
                'tutor_grado'    => $grupo->tutor_grado,
                // Alumnos
                'alumnos'        => $alumnos->values(),
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // =========================================================================

    /**
     * GET /api/grupos/{id}/alumnos
     *
     * Alumnos inscritos en un grupo con estatus y promedio calculado.
     * Campos: noControl, nombre, estatus, promedio
     */
    public function alumnos(int $id)
    {
        try {
            $grupo = DB::table('grupo')->where('id_grupo', $id)->first();

            if (!$grupo) {
                return response()->json(['error' => 'Grupo no encontrado'], 404);
            }

            $alumnos = DB::table('inscripcion as i')
                ->join('alumno as a',  'i.id_alumno', '=', 'a.id_alumno')
                ->join('persona as p', 'a.id_persona', '=', 'p.id_persona')
                ->leftJoin('estatus_alumno as ea', 'a.id_estatus_alumno', '=', 'ea.id_estatus_alumno')
                ->where('i.id_grupo', $id)
                ->whereIn('i.estatus', ['Activo', 'activo', 'inscrito'])
                ->select(
                    'a.numero_control as noControl',
                    DB::raw("CONCAT(p.nombre,' ',p.apellido_paterno,' ',COALESCE(p.apellido_materno,'')) as nombre"),
                    DB::raw("COALESCE(ea.nombre, a.estatus, 'Activo') as estatus"),
                    'i.id_inscripcion'
                )
                ->orderBy('p.apellido_paterno')
                ->get()
                ->map(function ($alumno) {
                    // Promedio ponderado de calificaciones del alumno en este grupo
                    $promedio = DB::table('calificacion as c')
                        ->join('evaluacion as ev', 'c.id_evaluacion', '=', 'ev.id_evaluacion')
                        ->where('c.id_inscripcion', $alumno->id_inscripcion)
                        ->sum(DB::raw('c.calificacion * ev.porcentaje / 100'));

                    return [
                        'noControl' => $alumno->noControl,
                        'nombre'    => trim($alumno->nombre),
                        'estatus'   => $alumno->estatus,
                        'promedio'  => $promedio ? round((float) $promedio, 1) : null,
                    ];
                });

            return response()->json([
                'id_grupo' => $id,
                'total'    => $alumnos->count(),
                'alumnos'  => $alumnos->values(),
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // =========================================================================

    /**
     * GET /api/grupos/{id}/calificaciones
     *
     * Calificaciones por unidad de todos los alumnos de un grupo.
     * Campos: noControl, nombre, unidad1, unidad2, unidad3, unidad4, promedio
     */
    public function calificaciones(int $id)
    {
        try {
            $grupo = DB::table('grupo')->where('id_grupo', $id)->first();

            if (!$grupo) {
                return response()->json(['error' => 'Grupo no encontrado'], 404);
            }

            // Evaluaciones del grupo indexadas por nombre normalizado
            $evaluaciones = DB::table('evaluacion')
                ->where('id_grupo', $id)
                ->get();

            $alumnos = DB::table('inscripcion as i')
                ->join('alumno as a',  'i.id_alumno',  '=', 'a.id_alumno')
                ->join('persona as p', 'a.id_persona', '=', 'p.id_persona')
                ->where('i.id_grupo', $id)
                ->whereIn('i.estatus', ['Activo', 'activo', 'inscrito'])
                ->select(
                    'a.numero_control as noControl',
                    DB::raw("CONCAT(p.nombre,' ',p.apellido_paterno,' ',COALESCE(p.apellido_materno,'')) as nombre"),
                    'i.id_inscripcion'
                )
                ->orderBy('p.apellido_paterno')
                ->get()
                ->map(function ($alumno) use ($evaluaciones) {

                    // Calificaciones del alumno indexadas por id_evaluacion
                    $califs = DB::table('calificacion')
                        ->where('id_inscripcion', $alumno->id_inscripcion)
                        ->pluck('calificacion', 'id_evaluacion');

                    // Buscar calificación por nombre de unidad
                    $getUnidad = function (int $n) use ($evaluaciones, $califs) {
                        $eval = $evaluaciones->first(function ($e) use ($n) {
                            $nombre = strtolower(trim($e->nombre));
                            return str_contains($nombre, 'unidad ' . $n)
                                || str_contains($nombre, 'u' . $n)
                                || str_contains($nombre, 'parcial ' . $n);
                        });
                        if (!$eval) return null;
                        $val = $califs->get($eval->id_evaluacion);
                        return $val !== null ? (float) $val : null;
                    };

                    $u1 = $getUnidad(1);
                    $u2 = $getUnidad(2);
                    $u3 = $getUnidad(3);
                    $u4 = $getUnidad(4);

                    // Promedio ponderado real
                    $promedio = DB::table('calificacion as c')
                        ->join('evaluacion as ev', 'c.id_evaluacion', '=', 'ev.id_evaluacion')
                        ->where('c.id_inscripcion', $alumno->id_inscripcion)
                        ->sum(DB::raw('c.calificacion * ev.porcentaje / 100'));

                    return [
                        'noControl' => $alumno->noControl,
                        'nombre'    => trim($alumno->nombre),
                        'unidad1'   => $u1,
                        'unidad2'   => $u2,
                        'unidad3'   => $u3,
                        'unidad4'   => $u4,
                        'promedio'  => $promedio ? round((float) $promedio, 1) : null,
                    ];
                });

            return response()->json([
                'id_grupo' => $id,
                'total'    => $alumnos->count(),
                'alumnos'  => $alumnos->values(),
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
