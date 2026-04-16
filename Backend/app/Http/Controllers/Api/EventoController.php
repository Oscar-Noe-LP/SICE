<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventoController extends Controller
{
    /**
     * GET /api/eventos
     * Lista de eventos para EventosView.vue
     */
    public function index(Request $request)
    {
        try {
            $nombre = trim($request->query('nombre', ''));
            $tipo   = trim($request->query('tipo', ''));

            $query = DB::table('evento as e')
                ->join('tipo_evento as t', 'e.id_tipo_evento', '=', 't.id_tipo_evento')
                ->leftJoin('participacion_evento as pe', 'e.id_evento', '=', 'pe.id_evento')
                ->select(
                    'e.id_evento',
                    'e.nombre_evento',
                    't.nombre_tipo',
                    'e.fecha',
                    'e.descripcion',
                    DB::raw('COUNT(pe.id_participacion) as participantes')
                )
                ->when($nombre !== '', function ($q) use ($nombre) {
                    $q->where('e.nombre_evento', 'like', '%' . $nombre . '%');
                })
                ->groupBy(
                    'e.id_evento',
                    'e.nombre_evento',
                    't.nombre_tipo',
                    'e.fecha',
                    'e.descripcion'
                )
                ->orderBy('e.fecha', 'asc');

            $eventos = $query->get()->map(function ($evento) use ($tipo) {
                $hoy = now()->toDateString();

                return [
                    'id'            => (int) $evento->id_evento,
                    'nombre'        => $evento->nombre_evento,
                    'tipo'          => $this->mapearTipo($evento->nombre_tipo),
                    'fecha'         => $evento->fecha,
                    'lugar'         => 'Por definir',
                    'descripcion'   => $evento->descripcion,
                    'participantes' => (int) $evento->participantes,
                    'cupo_maximo'   => null,
                    'activo'        => $evento->fecha >= $hoy,
                ];
            });

            if ($tipo !== '') {
                $eventos = $eventos->filter(function ($evento) use ($tipo) {
                    return $evento['tipo'] === $tipo;
                })->values();
            }

            return response()->json($eventos, 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al obtener eventos',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/tipos-evento
     */
    public function tiposEvento()
    {
        try {
            $tipos = DB::table('tipo_evento')
                ->select(
                    'id_tipo_evento as id',
                    DB::raw("
                        CASE
                            WHEN nombre_tipo IN ('Conferencia', 'Taller', 'Seminario') THEN 'Académico'
                            WHEN nombre_tipo = 'Evento Cultural' THEN 'Cultural'
                            WHEN nombre_tipo = 'Evento Deportivo' THEN 'Deportivo'
                            WHEN nombre_tipo = 'Concurso' THEN 'Institucional'
                            ELSE nombre_tipo
                        END as nombre
                    ")
                )
                ->get();

            $tiposUnicos = $tipos->unique('nombre')->values();

            return response()->json($tiposUnicos, 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al obtener tipos de evento',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/eventos/{id}
     */
    public function show($id)
    {
        try {
            $evento = DB::table('evento as e')
                ->join('tipo_evento as t', 'e.id_tipo_evento', '=', 't.id_tipo_evento')
                ->leftJoin('participacion_evento as pe', 'e.id_evento', '=', 'pe.id_evento')
                ->select(
                    'e.id_evento',
                    'e.nombre_evento',
                    't.nombre_tipo',
                    'e.fecha',
                    'e.descripcion',
                    DB::raw('COUNT(pe.id_participacion) as participantes')
                )
                ->where('e.id_evento', $id)
                ->groupBy(
                    'e.id_evento',
                    'e.nombre_evento',
                    't.nombre_tipo',
                    'e.fecha',
                    'e.descripcion'
                )
                ->first();

            if (!$evento) {
                return response()->json([
                    'message' => 'Evento no encontrado'
                ], 404);
            }

            return response()->json([
                'id'            => (int) $evento->id_evento,
                'nombre'        => $evento->nombre_evento,
                'tipo'          => $this->mapearTipo($evento->nombre_tipo),
                'fecha'         => $evento->fecha,
                'lugar'         => 'Por definir',
                'descripcion'   => $evento->descripcion,
                'participantes' => (int) $evento->participantes,
                'cupo_maximo'   => null,
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al obtener el evento',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/eventos/{id}/participantes
     */
    public function participantes($id)
    {
        try {
            $existeEvento = DB::table('evento')
                ->where('id_evento', $id)
                ->exists();

            if (!$existeEvento) {
                return response()->json([
                    'message' => 'Evento no encontrado'
                ], 404);
            }

            $participantes = DB::table('participacion_evento as pe')
                ->join('alumno as a', 'pe.id_alumno', '=', 'a.id_alumno')
                ->join('persona as p', 'a.id_persona', '=', 'p.id_persona')
                ->join('carrera as c', 'a.id_carrera', '=', 'c.id_carrera')
                ->select(
                    'a.numero_control',
                    'p.nombre',
                    'p.apellido_paterno',
                    'p.apellido_materno',
                    'c.nombre as carrera',
                    'a.semestre_actual',
                    'pe.constancia_emitida'
                )
                ->where('pe.id_evento', $id)
                ->orderBy('a.numero_control')
                ->get()
                ->map(function ($row) {
                    $nombreCompleto = trim(
                        ($row->nombre ?? '') . ' ' .
                        ($row->apellido_paterno ?? '') . ' ' .
                        ($row->apellido_materno ?? '')
                    );

                    return [
                        'control'            => $row->numero_control,
                        'nombre'             => $nombreCompleto,
                        'carrera'            => $row->carrera,
                        'semestre'           => (int) $row->semestre_actual,
                        'constancia_emitida' => (bool) $row->constancia_emitida,
                    ];
                });

            return response()->json($participantes, 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al obtener participantes',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/eventos/{id}/participantes
     */
    public function registrarParticipante(Request $request, $id)
    {
        try {
            $request->validate([
                'no_control' => 'required|string|max:20'
            ]);

            $evento = DB::table('evento')
                ->where('id_evento', $id)
                ->first();

            if (!$evento) {
                return response()->json([
                    'message' => 'Evento no encontrado'
                ], 404);
            }

            $alumno = DB::table('alumno')
                ->where('numero_control', $request->no_control)
                ->first();

            if (!$alumno) {
                return response()->json([
                    'message' => 'Alumno no encontrado'
                ], 404);
            }

            $yaRegistrado = DB::table('participacion_evento')
                ->where('id_evento', $id)
                ->where('id_alumno', $alumno->id_alumno)
                ->exists();

            if ($yaRegistrado) {
                return response()->json([
                    'message' => 'El alumno ya está registrado en este evento'
                ], 409);
            }

            DB::table('participacion_evento')->insert([
                'id_evento'          => $id,
                'id_alumno'          => $alumno->id_alumno,
                'constancia_emitida' => false,
            ]);

            return response()->json([
                'message' => 'Participante registrado correctamente'
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Datos inválidos',
                'errors'  => $e->errors()
            ], 422);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al registrar participante',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * PATCH /api/eventos/{id}/participantes/{control}/constancia
     */
    public function emitirConstancia($id, $control)
    {
        try {
            $alumno = DB::table('alumno')
                ->where('numero_control', $control)
                ->first();

            if (!$alumno) {
                return response()->json([
                    'message' => 'Alumno no encontrado'
                ], 404);
            }

            $participacion = DB::table('participacion_evento')
                ->where('id_evento', $id)
                ->where('id_alumno', $alumno->id_alumno)
                ->first();

            if (!$participacion) {
                return response()->json([
                    'message' => 'Participación no encontrada'
                ], 404);
            }

            DB::table('participacion_evento')
                ->where('id_evento', $id)
                ->where('id_alumno', $alumno->id_alumno)
                ->update([
                    'constancia_emitida' => true
                ]);

            return response()->json([
                'message' => 'Constancia emitida correctamente'
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al emitir constancia',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/eventos/{id}/participantes/{control}
     */
    public function eliminarParticipante($id, $control)
    {
        try {
            $alumno = DB::table('alumno')
                ->where('numero_control', $control)
                ->first();

            if (!$alumno) {
                return response()->json([
                    'message' => 'Alumno no encontrado'
                ], 404);
            }

            $eliminado = DB::table('participacion_evento')
                ->where('id_evento', $id)
                ->where('id_alumno', $alumno->id_alumno)
                ->delete();

            if (!$eliminado) {
                return response()->json([
                    'message' => 'Participación no encontrada'
                ], 404);
            }

            return response()->json([
                'message' => 'Participante eliminado correctamente'
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al eliminar participante',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/alumnos/buscar?no_control=...
     */
    public function buscarAlumno(Request $request)
    {
        try {
            $noControl = trim($request->query('no_control', ''));

            if ($noControl === '') {
                return response()->json([
                    'message' => 'Debes proporcionar un número de control'
                ], 422);
            }

            $alumno = DB::table('alumno as a')
                ->join('persona as p', 'a.id_persona', '=', 'p.id_persona')
                ->join('carrera as c', 'a.id_carrera', '=', 'c.id_carrera')
                ->select(
                    'a.numero_control',
                    'p.nombre',
                    'p.apellido_paterno',
                    'p.apellido_materno',
                    'c.nombre as carrera',
                    'a.semestre_actual'
                )
                ->where('a.numero_control', $noControl)
                ->first();

            if (!$alumno) {
                return response()->json(null, 404);
            }

            $nombreCompleto = trim(
                ($alumno->nombre ?? '') . ' ' .
                ($alumno->apellido_paterno ?? '') . ' ' .
                ($alumno->apellido_materno ?? '')
            );

            return response()->json([
                'control'  => $alumno->numero_control,
                'nombre'   => $nombreCompleto,
                'carrera'  => $alumno->carrera,
                'semestre' => (int) $alumno->semestre_actual,
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al buscar alumno',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mapea los tipos de BD a los tipos visuales esperados por el front.
     */
    private function mapearTipo(string $tipoBd): string
    {
        return match ($tipoBd) {
            'Conferencia', 'Taller', 'Seminario' => 'Académico',
            'Evento Cultural'                    => 'Cultural',
            'Evento Deportivo'                   => 'Deportivo',
            'Concurso'                           => 'Institucional',
            default                              => $tipoBd,
        };
    }
}