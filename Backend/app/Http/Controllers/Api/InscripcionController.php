<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alumno;
use App\Models\Grupo;
use App\Models\Inscripcion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InscripcionController extends Controller
{
    public function buscarAlumno(Request $request)
    {
        try {
            $term = trim($request->query('q', ''));

            if ($term === '') {
                return response()->json([
                    'error' => 'Debes escribir un nombre o número de control'
                ], 422);
            }

            $alumno = Alumno::with(['persona', 'carrera'])
                ->where('numero_control', $term)
                ->orWhereHas('persona', function ($query) use ($term) {
                    $query->where('nombre', 'like', "%{$term}%")
                          ->orWhere('apellido_paterno', 'like', "%{$term}%")
                          ->orWhere('apellido_materno', 'like', "%{$term}%");
                })
                ->first();

            if (!$alumno) {
                return response()->json([
                    'error' => 'Alumno no encontrado'
                ], 404);
            }

            return response()->json([
                'id_alumno' => $alumno->id_alumno,
                'noControl' => $alumno->numero_control,
                'nombre' => trim(
                    ($alumno->persona->nombre ?? '') . ' ' .
                    ($alumno->persona->apellido_paterno ?? '') . ' ' .
                    ($alumno->persona->apellido_materno ?? '')
                ),
                'carrera' => $alumno->carrera->nombre ?? 'N/A',
                'semestre' => $alumno->semestre_actual
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al buscar alumno: ' . $e->getMessage()
            ], 500);
        }
    }

    public function gruposDisponibles()
    {
        try {
            $grupos = DB::table('grupo as g')
                ->join('materia as m', 'g.id_materia', '=', 'm.id_materia')
                ->leftJoin('docente as d', 'g.id_docente', '=', 'd.id_docente')
                ->leftJoin('empleado as e', 'd.id_empleado', '=', 'e.id_empleado')
                ->leftJoin('persona as p', 'e.id_persona', '=', 'p.id_persona')
                ->leftJoin('aula as a', 'g.id_aula', '=', 'a.id_aula')
                ->leftJoin('inscripcion as i', 'g.id_grupo', '=', 'i.id_grupo')
                ->select(
                    'g.id_grupo as id',
                    'g.clave_grupo',
                    'm.nombre as materia',
                    DB::raw("COALESCE(TRIM(CONCAT(
                        COALESCE(p.nombre, ''), ' ',
                        COALESCE(p.apellido_paterno, ''), ' ',
                        COALESCE(p.apellido_materno, '')
                    )), 'Sin docente') as docente"),
                    DB::raw("COALESCE(a.nombre, 'Sin aula') as aula"),
                    'g.capacidad',
                    DB::raw('COUNT(i.id_inscripcion) as inscritos')
                )
                ->groupBy(
                    'g.id_grupo',
                    'g.clave_grupo',
                    'm.nombre',
                    'p.nombre',
                    'p.apellido_paterno',
                    'p.apellido_materno',
                    'a.nombre',
                    'g.capacidad'
                )
                ->orderBy('m.nombre')
                ->get();

            return response()->json($grupos);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al cargar grupos: ' . $e->getMessage()
            ], 500);
        }
    }

    public function inscribirAlumno(Request $request)
    {
        try {
            $request->validate([
                'id_alumno' => 'required|integer|exists:alumno,id_alumno',
                'id_grupo'  => 'required|integer|exists:grupo,id_grupo',
            ]);

            $grupo = Grupo::where('id_grupo', $request->id_grupo)->first();

            if (!$grupo) {
                return response()->json([
                    'error' => 'Grupo no encontrado'
                ], 404);
            }

            $yaInscrito = Inscripcion::where('id_alumno', $request->id_alumno)
                ->where('id_grupo', $request->id_grupo)
                ->exists();

            if ($yaInscrito) {
                return response()->json([
                    'error' => 'El alumno ya está inscrito en este grupo'
                ], 409);
            }

            $totalInscritos = Inscripcion::where('id_grupo', $request->id_grupo)->count();

            if ($totalInscritos >= $grupo->capacidad) {
                return response()->json([
                    'error' => 'El grupo ya no tiene cupo disponible'
                ], 409);
            }

            // Validar prerrequisitos
            $idMateria = DB::table('grupo')->where('id_grupo', $request->id_grupo)->value('id_materia');
            $prerrequisitos = DB::table('prerrequisito')
                ->where('id_materia', $idMateria)
                ->pluck('id_materia_prerrequisito');

            if ($prerrequisitos->isNotEmpty()) {
                foreach ($prerrequisitos as $idPre) {
                    $aprobado = DB::table('inscripcion as i')
                        ->join('grupo as g', 'i.id_grupo', '=', 'g.id_grupo')
                        ->join('calificacion as cal', 'cal.id_inscripcion', '=', 'i.id_inscripcion')
                        ->where('i.id_alumno', $request->id_alumno)
                        ->where('g.id_materia', $idPre)
                        ->where('cal.calificacion', '>=', 70)
                        ->exists();

                    if (!$aprobado) {
                        $nombrePre = DB::table('materia')->where('id_materia', $idPre)->value('nombre');
                        return response()->json([
                            'error' => "No cumple el prerrequisito: {$nombrePre}"
                        ], 422);
                    }
                }
            }

            // Validar traslape de horario
            if ($grupo->dia && $grupo->hora_inicio && $grupo->hora_fin) {
                $traslape = DB::table('inscripcion as i')
                    ->join('grupo as g', 'i.id_grupo', '=', 'g.id_grupo')
                    ->where('i.id_alumno', $request->id_alumno)
                    ->where('i.estatus', 'Activo')
                    ->where('g.id_periodo', $grupo->id_periodo)
                    ->where('g.dia', $grupo->dia)
                    ->where('g.id_grupo', '!=', $request->id_grupo)
                    ->where(function ($q) use ($grupo) {
                        $q->whereBetween('g.hora_inicio', [$grupo->hora_inicio, $grupo->hora_fin])
                          ->orWhereBetween('g.hora_fin', [$grupo->hora_inicio, $grupo->hora_fin])
                          ->orWhere(function ($q2) use ($grupo) {
                              $q2->where('g.hora_inicio', '<=', $grupo->hora_inicio)
                                 ->where('g.hora_fin', '>=', $grupo->hora_fin);
                          });
                    })
                    ->exists();

                if ($traslape) {
                    return response()->json([
                        'error' => 'El alumno ya tiene una clase en ese horario'
                    ], 409);
                }
            }

            // Validar carga académica máxima (máx. 7 materias por periodo)
            $cargaActual = DB::table('inscripcion as i')
                ->join('grupo as g', 'i.id_grupo', '=', 'g.id_grupo')
                ->where('i.id_alumno', $request->id_alumno)
                ->where('g.id_periodo', $grupo->id_periodo)
                ->whereIn('i.estatus', ['Activo', 'activo', 'inscrito'])
                ->count();

            if ($cargaActual >= 7) {
                return response()->json([
                    'error' => 'El alumno ya alcanzó el límite máximo de materias por periodo (7)'
                ], 422);
            }

            // Validar adeudos pendientes
            $tieneAdeudos = DB::table('adeudo')
                ->where('id_alumno', $request->id_alumno)
                ->where('pagado', false)
                ->exists();

            if ($tieneAdeudos) {
                return response()->json([
                    'error' => 'El alumno tiene adeudos pendientes y no puede inscribirse'
                ], 422);
            }

            // Validar que no tenga ya aprobada la materia
            $yaAprobada = DB::table('inscripcion as i')
                ->join('grupo as g', 'i.id_grupo', '=', 'g.id_grupo')
                ->join('calificacion as cal', 'cal.id_inscripcion', '=', 'i.id_inscripcion')
                ->where('i.id_alumno', $request->id_alumno)
                ->where('g.id_materia', $idMateria)
                ->where('cal.calificacion', '>=', 70)
                ->exists();

            if ($yaAprobada) {
                return response()->json([
                    'error' => 'El alumno ya tiene esta materia aprobada'
                ], 409);
            }

            $inscripcion = Inscripcion::create([
                'id_alumno' => $request->id_alumno,
                'id_grupo' => $request->id_grupo,
                'fecha_inscripcion' => now()->format('Y-m-d'),
                'estatus' => 'Activo'
            ]);

            return response()->json([
                'message' => 'Inscripción realizada correctamente',
                'id' => $inscripcion->id_inscripcion
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Datos inválidos',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al realizar la inscripción: ' . $e->getMessage()
            ], 500);
        }
    }

    // =====================================================
    // 🔹 LISTADO DE INSCRIPCIONES
    // GET /api/form/inscripciones
    // =====================================================
    public function index()
    {
        $data = DB::table('inscripcion as i')
            ->join('alumno as a', 'i.id_alumno', '=', 'a.id_alumno')
            ->join('persona as p', 'a.id_persona', '=', 'p.id_persona')
            ->join('carrera as c', 'a.id_carrera', '=', 'c.id_carrera')
            ->join('grupo as g', 'i.id_grupo', '=', 'g.id_grupo')
            ->join('materia as m', 'g.id_materia', '=', 'm.id_materia')
            ->join('periodo as pe', 'g.id_periodo', '=', 'pe.id_periodo')
            ->leftJoin('docente as d', 'g.id_docente', '=', 'd.id_docente')
            ->leftJoin('empleado as e', 'd.id_empleado', '=', 'e.id_empleado')
            ->leftJoin('persona as pd', 'e.id_persona', '=', 'pd.id_persona')
            ->leftJoin('aula as au', 'g.id_aula', '=', 'au.id_aula')
            ->select(
                'i.id_inscripcion',
                'i.id_alumno',
                'i.id_grupo',
                'a.numero_control',
                DB::raw("CONCAT(p.nombre,' ',p.apellido_paterno,' ',p.apellido_materno) as nombre_alumno"),
                'c.id_carrera',
                'c.nombre as nombre_carrera',
                'g.clave_grupo',
                'm.nombre as nombre_materia',
                'pe.id_periodo',
                'pe.nombre_periodo',
                DB::raw("CONCAT(pd.nombre,' ',pd.apellido_paterno,' ',pd.apellido_materno) as nombre_docente"),
                'au.nombre as nombre_aula',
                'i.fecha_inscripcion',
                'i.estatus'
            )
            ->get();

        return response()->json($data);
    }

    // =====================================================
    // 🔹 KPIs
    // GET /api/form/kpis
    // =====================================================
    public function kpis()
    {
        $data = DB::table('inscripcion')
            ->select('estatus', DB::raw('COUNT(*) as total'))
            ->groupBy('estatus')
            ->get();

        $kpis = [
            'totalInscritos' => $data->sum('total'),
            'activos' => 0,
            'bajaTemporal' => 0,
            'bajaDefinitiva' => 0
        ];

        foreach ($data as $row) {
            if ($row->estatus === 'Activo') $kpis['activos'] = $row->total;
            if ($row->estatus === 'Baja Temporal') $kpis['bajaTemporal'] = $row->total;
            if ($row->estatus === 'Baja Definitiva') $kpis['bajaDefinitiva'] = $row->total;
        }

        return response()->json($kpis);
    }

    // =====================================================
    // 🔹 PERIODOS
    // GET /api/form/periodos
    // =====================================================
    public function periodos()
    {
        return response()->json(
            DB::table('periodo')->select('id_periodo', 'nombre_periodo')->get()
        );
    }

    // =====================================================
    // 🔹 CARRERAS
    // GET /api/form/carreras
    // =====================================================
    public function carreras()
    {
        return response()->json(
            DB::table('carrera')->select('id_carrera', 'nombre')->get()
        );
    }


    // =====================================================
    // 🔹 CREAR INSCRIPCION
    // POST /api/form/inscripciones
    // =====================================================
    public function store(Request $request)
    {
        $request->validate([
            'id_alumno' => 'required',
            'id_grupo' => 'required',
            'fecha_inscripcion' => 'required|date',
            'estatus' => 'required'
        ]);

        // validar duplicado
        $existe = DB::table('inscripcion')
            ->where('id_alumno', $request->id_alumno)
            ->where('id_grupo', $request->id_grupo)
            ->exists();

        if ($existe) {
            return response()->json([
                'message' => 'El alumno ya está inscrito en ese grupo'
            ], 400);
        }

        DB::table('inscripcion')->insert([
            'id_alumno' => $request->id_alumno,
            'id_grupo' => $request->id_grupo,
            'fecha_inscripcion' => $request->fecha_inscripcion,
            'estatus' => $request->estatus
        ]);

        return response()->json([
            'message' => 'Inscripción creada correctamente'
        ]);
    }

    // =====================================================
    // 🔹 ACTUALIZAR (SOLO ESTATUS)
    // PUT /api/form/inscripciones/{id}
    // =====================================================
    public function update(Request $request, $id)
    {
        $request->validate([
            'estatus' => 'required'
        ]);

        DB::table('inscripcion')
            ->where('id_inscripcion', $id)
            ->update([
                'estatus' => $request->estatus
            ]);

        return response()->json([
            'message' => 'Inscripción actualizada'
        ]);
    }

    // =====================================================
    // 🔹 ELIMINAR
    // DELETE /api/form/inscripciones/{id}
    // =====================================================
    public function destroy($id)
    {
        DB::table('inscripcion')
            ->where('id_inscripcion', $id)
            ->delete();

        return response()->json([
            'message' => 'Eliminado correctamente'
        ]);
    }
}
