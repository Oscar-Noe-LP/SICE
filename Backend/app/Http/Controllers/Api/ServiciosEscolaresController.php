<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alumno;
use App\Models\Inscripcion;
use App\Models\Persona;
use App\Models\Carrera;
use App\Models\Calificacion;
use App\Models\Grupo;
use App\Models\Evaluacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiciosEscolaresController extends Controller
{
    /**
     * DASHBOARD: Resumen estadístico
     */
    public function getResumen()
    {
        try {
            return response()->json([
                'total_alumnos'       => Alumno::count(),
                'total_inscripciones' => Inscripcion::count(),
                
                'recientes'           => Inscripcion::with(['alumno.persona', 'alumno.carrera'])
                    ->orderBy('id_inscripcion', 'desc')
                    ->take(5)
                    ->get()
                    ->map(function ($ins) {
                        return [
                            'id_inscripcion'    => $ins->id_inscripcion,
                            'noControl'         => $ins->alumno->numero_control ?? 'N/A',
                            'nombre'            => ($ins->alumno->persona->nombre ?? '') . ' ' . ($ins->alumno->persona->apellido_paterno ?? ''),
                            'carrera'           => $ins->alumno->carrera->nombre ?? 'N/A',
                            'semestre'          => $ins->alumno->semestre_actual ?? 0,
                            'fecha'             => $ins->fecha_inscripcion,
                            'estatus'           => $ins->estatus
                        ];
                    })
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * ALUMNOS: Lista completa y creación
     */
    public function getAlumnos()
    {
        try {
            $alumnos = Alumno::with(['persona', 'carrera'])
                ->get()
                ->map(function ($alumno) {
                    return [
                        'id'        => $alumno->id_alumno,
                        'noControl' => $alumno->numero_control,
                        'nombre'    => trim(($alumno->persona->nombre ?? 'Sin nombre') . ' ' . ($alumno->persona->apellido_paterno ?? '')),
                        'carrera'   => $alumno->carrera->nombre ?? 'Sin carrera asignada',
                        'semestre'  => $alumno->semestre_actual,
                        'estatus'   => $alumno->estatus == 1 ? 'Activo' : ($alumno->estatus == 2 ? 'Baja Temporal' : 'Baja Definitiva'),
                    ];
                });
            return response()->json($alumnos);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $persona = Persona::create([
                    'nombre'           => $request->nombre,
                    'apellido_paterno' => $request->apellidoPaterno,
                    'apellido_materno' => $request->apellidoMaterno,
                    'curp'             => 'POR DEFINIR',
                    'fecha_nacimiento' => '2000-01-01',
                    'id_genero'        => $request->genero === 'Masculino' ? 1 : 2,
                ]);

                $carrera = Carrera::where('nombre', $request->carrera)->first();

                $alumno = Alumno::create([
                    'id_persona'      => $persona->id_persona,
                    'numero_control'  => $request->noControl,
                    'id_carrera'      => $carrera ? $carrera->id_carrera : null,
                    'fecha_ingreso'   => $request->fechaIngreso,
                    'semestre_actual' => $request->semestre,
                    'estatus'         => $request->estatus === 'Activo' ? 1 : ($request->estatus === 'Baja Temporal' ? 2 : 0),
                ]);

                return response()->json(['message' => 'Alumno guardado correctamente', 'id' => $alumno->id_alumno], 201);
            });
        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudo guardar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * INSCRIPCIÓN: Búsqueda y registro
     */
    public function buscarAlumnoInscripcion(Request $request)
    {
        $term = $request->query('q');
        $alumno = Alumno::with(['persona', 'carrera'])
            ->where('numero_control', $term)
            ->orWhereHas('persona', function($query) use ($term) {
                $query->where('nombre', 'like', "%$term%");
            })->first();

        if (!$alumno) return response()->json(['error' => 'Alumno no encontrado'], 404);

        return response()->json([
            'id_alumno' => $alumno->id_alumno,
            'noControl' => $alumno->numero_control,
            'nombre'    => $alumno->persona->nombre . ' ' . $alumno->persona->apellido_paterno,
            'carrera'   => $alumno->carrera->nombre ?? 'N/A',
            'semestre'  => $alumno->semestre_actual
        ]);
    }

    public function getGruposDisponibles()
    {
        // Puedes cambiar esto por una consulta real a la tabla 'grupo'
        return response()->json([
            ['id' => 1, 'materia' => 'Algoritmos', 'docente' => 'Mtro. Juan Morales', 'aula' => 'A-201', 'capacidad' => 30, 'inscritos' => 23],
            ['id' => 2, 'materia' => 'Base de Datos', 'docente' => 'Dra. Ana Ruiz', 'aula' => 'B-103', 'capacidad' => 30, 'inscritos' => 28],
            ['id' => 3, 'materia' => 'Redes', 'docente' => 'Mtro. Carlos Jiménez', 'aula' => 'A-204', 'capacidad' => 25, 'inscritos' => 19]
        ]);
    }

    public function inscribirAlumno(Request $request)
    {
        try {
            $inscripcion = Inscripcion::create([
                'id_alumno' => $request->id_alumno,
                'id_periodo' => 1,
                'fecha_inscripcion' => now(),
                'estatus' => 'ACTIVO'
            ]);
            return response()->json(['message' => 'Inscripción exitosa', 'id' => $inscripcion->id_inscripcion]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * EVALUACIONES: Criterios de evaluación
     */
    public function getEvaluaciones($id_grupo)
    {
        try {
            $evaluaciones = Evaluacion::where('id_grupo', $id_grupo)->get();
            return response()->json($evaluaciones);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function guardarEvaluaciones(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                Evaluacion::where('id_grupo', $request->id_grupo)->delete();
                foreach ($request->criterios as $criterio) {
                    Evaluacion::create([
                        'id_grupo'   => $request->id_grupo,
                        'nombre'     => $criterio['nombre'],
                        'porcentaje' => $criterio['porcentaje']
                    ]);
                }
            });
            return response()->json(['message' => 'Configuración de evaluación guardada']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * CALIFICACIONES: Captura de notas
     */
    public function getCalificacionesGrupo() 
    {
        try {
            $datos = Inscripcion::with(['alumno.persona'])
                ->get()
                ->map(function($ins) {
                    return [
                        'id_inscripcion' => $ins->id_inscripcion,
                        'control'        => $ins->alumno->numero_control ?? 'N/A',
                        'nombre'         => ($ins->alumno->persona->nombre ?? '') . ' ' . ($ins->alumno->persona->apellido_paterno ?? ''),
                        'p1'             => $ins->parcial_1 ?? 0, 
                        'p2'             => $ins->parcial_2 ?? 0,
                        'proy'           => $ins->proyecto ?? 0
                    ];
                });
            return response()->json($datos);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function guardarCalificaciones(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                foreach ($request->alumnos as $alumnoData) {
                    $final = (floatval($alumnoData['p1']) * 0.3) + 
                             (floatval($alumnoData['p2']) * 0.3) + 
                             (floatval($alumnoData['proy']) * 0.4);

                    // Buscar la evaluacion final del grupo
                    $evaluacion = \App\Models\Evaluacion::where('id_grupo', $alumnoData['id_grupo'] ?? null)
                        ->where('nombre', 'Final')
                        ->first();
                    $id_evaluacion = $evaluacion ? $evaluacion->id_evaluacion : 1;

                    \App\Models\Calificacion::updateOrCreate(
                        [
                            'id_inscripcion' => $alumnoData['id_inscripcion'],
                            'id_evaluacion' => $id_evaluacion
                        ],
                        [
                            'calificacion'  => $final
                        ]
                    );
                }
            });
            return response()->json(['message' => 'Calificaciones guardadas exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al guardar notas: ' . $e->getMessage()], 500);
        }
    }
}