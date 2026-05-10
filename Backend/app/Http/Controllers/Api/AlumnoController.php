<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alumno;
use App\Models\Persona;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AlumnoController extends Controller
{
    public function index()
    {
        try {
            // Se incluye estatusAlumno para el campo "estatus" 
            $alumnos = Alumno::with(['persona', 'carrera', 'estatusAlumno'])->get();

            return $alumnos->map(function ($a) {
                return [
                    'id_alumno'         => $a->id_alumno,
                    'numero_control'    => $a->numero_control,
                    'id_carrera'        => $a->id_carrera,
                    'semestre_actual'   => $a->semestre_actual,
                    'fecha_ingreso'     => $a->fecha_ingreso,
                    'id_estatus_alumno' => $a->id_estatus_alumno,
                    // "estatus" siempre es el nombre legible; fallback a Activo/Inactivo
                    'estatus'           => $a->estatusAlumno?->nombre
                                          ?? ($a->estatus ? 'Activo' : 'Inactivo'),
                    'persona'           => $a->persona,
                    // Se expone el objeto carrera con nombre_carrera para compatibilidad
                    // con el template del Vue (alumno.carrera?.nombre_carrera)
                    'carrera'           => $a->carrera ? [
                        'id_carrera'    => $a->carrera->id_carrera,
                        'nombre_carrera'=> $a->carrera->nombre,   
                        'nombre'        => $a->carrera->nombre,
                    ] : null,
                    // nombre aplanado para facilitar búsqueda en el frontend
                    'nombre'            => trim(
                                            ($a->persona->nombre          ?? '') . ' ' .
                                            ($a->persona->apellido_paterno ?? '') . ' ' .
                                            ($a->persona->apellido_materno ?? '')
                                          ),
                ];
            });
        } catch (\Exception $e) {
            Log::error("Error index alumnos: " . $e->getMessage());
            return response()->json(['error' => 'Error al cargar alumnos'], 500);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'numero_control'   => 'required|string|unique:alumno,numero_control',
                'nombre'           => 'required|string',
                'apellido_paterno' => 'required|string',
                'genero'           => 'required|in:Masculino,Femenino,Otro',
                'id_carrera'       => 'required|integer|exists:carrera,id_carrera',
                'semestre_actual'  => 'required|integer|between:1,8',
                'estatus'          => 'required',
                'fecha_ingreso'    => 'required|date',
            ]);

            $idGenero = match ($request->genero) {
                'Masculino' => 1,
                'Femenino'  => 2,
                default     => 3,
            };

            // Crear Persona
            $persona = Persona::create([
                'nombre'           => $request->nombre,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno ?? null,
                'id_genero'        => $idGenero,
                'curp'             => $request->curp ?? null,
                'fecha_nacimiento' => $request->fecha_nacimiento ?? null,
            ]);

            // Crear Alumno
            $alumno = Alumno::create([
                'numero_control'   => $request->numero_control,
                'id_persona'       => $persona->id_persona,
                'id_carrera'       => $request->id_carrera,
                'semestre_actual'  => $request->semestre_actual,
                'estatus'          => $request->estatus,          
                'fecha_ingreso'    => $request->fecha_ingreso,
            ]);

            DB::commit();

            BitacoraService::registrar('INSERT', 'alumno', $alumno->id_alumno, [], [
                'numero_control' => $request->numero_control,
                'id_carrera'     => $request->id_carrera,
                'semestre'       => $request->semestre_actual,
            ]);

            return response()->json([
                'message' => 'Alumno registrado correctamente',
                'data'    => $alumno->load(['persona', 'carrera'])
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json(['error' => 'Datos inválidos', 'detalle' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al crear alumno: " . $e->getMessage());
            return response()->json([
                'error'   => 'Error interno del servidor',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            Log::info("Actualizando alumno ID: {$id}", $request->all());

            $alumno = Alumno::findOrFail($id);

            if ($alumno->persona) {
                $nombreCompleto = explode(' ', trim($request->nombre));
                $nombre           = $nombreCompleto[0] ?? '';
                $apellido_paterno = $nombreCompleto[1] ?? '';
                $apellido_materno = implode(' ', array_slice($nombreCompleto, 2));

                $alumno->persona->update([
                    'nombre'           => $nombre,
                    'apellido_paterno' => $apellido_paterno,
                    'apellido_materno' => $apellido_materno,
                ]);
            }

         
            $estatusBoolean = match($request->estatus) {
                'Activo'          => 1,
                'Baja Temporal'   => 0,
                'Baja Definitiva' => 0,
                default           => $alumno->estatus
            };

            $alumno->update([
                'id_carrera'        => $request->id_carrera      ?? $alumno->id_carrera,
                'semestre_actual'   => $request->semestre_actual  ?? $alumno->semestre_actual,
                'estatus'           => $estatusBoolean,
                'id_estatus_alumno' => $request->id_estatus_alumno ?? $alumno->id_estatus_alumno,
            ]);

            DB::commit();

            BitacoraService::registrar('UPDATE', 'alumno', $id,
                ['id_carrera' => $alumno->getOriginal('id_carrera'), 'semestre_actual' => $alumno->getOriginal('semestre_actual'), 'estatus' => $alumno->getOriginal('estatus')],
                ['id_carrera' => $request->id_carrera, 'semestre_actual' => $request->semestre_actual, 'estatus' => $request->estatus]
            );

            // Recargar con la relación de estatus para que la respuesta incluya el nombre correcto
            $alumno->load(['persona', 'carrera', 'estatusAlumno']);
            $alumno->estatus = $alumno->estatusAlumno?->nombre
                               ?? ($alumno->estatus ? 'Activo' : 'Inactivo');

            return response()->json([
                'message' => 'Alumno actualizado con éxito',
                'data'    => $alumno
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al actualizar alumno ID {$id}: " . $e->getMessage());
            return response()->json([
                'error'   => 'No se pudo actualizar el alumno',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $alumno = Alumno::with('persona')->findOrFail($id);
            $idPersona = $alumno->id_persona;

            // ==================== 1. KARDEX (agregado en correcion2.sql) ====================
            // detalle_kardex referencia kardex, que referencia alumno → hay que borrar primero
            $kardexIds = DB::table('kardex')
                          ->where('id_alumno', $id)
                          ->pluck('id_kardex');

            if ($kardexIds->isNotEmpty()) {
                DB::table('detalle_kardex')->whereIn('id_kardex', $kardexIds)->delete();
                DB::table('kardex')->whereIn('id_kardex', $kardexIds)->delete();
            }

            // ==================== 2. CALIFICACIONES E INSCRIPCIONES ====================
            // calificacion referencia inscripcion → borrar calificacion primero
            $inscripcionIds = DB::table('inscripcion')
                               ->where('id_alumno', $id)
                               ->pluck('id_inscripcion');

            if ($inscripcionIds->isNotEmpty()) {
                DB::table('calificacion')->whereIn('id_inscripcion', $inscripcionIds)->delete();
            }
            DB::table('inscripcion')->where('id_alumno', $id)->delete();

            // ==================== 3. PARTICIPACIÓN EN EVENTOS ====================
            DB::table('participacion_evento')->where('id_alumno', $id)->delete();

            // ==================== 4. REGISTRO DE ALUMNO ====================
            DB::table('alumno')->where('id_alumno', $id)->delete();

            // ==================== 5. DATOS DE CONTACTO DE PERSONA ====================
            DB::table('persona_correo')->where('id_persona', $idPersona)->delete();
            DB::table('persona_telefono')->where('id_persona', $idPersona)->delete();
            DB::table('persona_direccion')->where('id_persona', $idPersona)->delete();

            // ==================== 6. COMITÉ ACADÉMICO ====================
            // resolucion_comite referencia solicitud_comite → borrar resoluciones primero
            $solicitudIds = DB::table('solicitud_comite')
                             ->where('id_persona', $idPersona)
                             ->pluck('id_solicitud');

            if ($solicitudIds->isNotEmpty()) {
                DB::table('resolucion_comite')->whereIn('id_solicitud', $solicitudIds)->delete();
                DB::table('solicitud_comite')->whereIn('id_solicitud', $solicitudIds)->delete();
            }

            // ==================== 7. USUARIOS Y BITÁCORA ====================
            $usuarioIds = DB::table('usuario')
                           ->where('id_persona', $idPersona)
                           ->pluck('id_usuario');

            if ($usuarioIds->isNotEmpty()) {
                DB::table('bitacora')->whereIn('id_usuario', $usuarioIds)->delete();
                DB::table('usuario_rol')->whereIn('id_usuario', $usuarioIds)->delete();
                DB::table('usuario')->whereIn('id_usuario', $usuarioIds)->delete();
            }

            // ==================== 8. EMPLEADO / DOCENTE ====================
            // Si la persona también era empleado/docente, se limpia esa relación.
            // Se borra en orden: adscripcion y docente (que referencian empleado) antes que empleado.
            $empleadoIds = DB::table('empleado')
                            ->where('id_persona', $idPersona)
                            ->pluck('id_empleado');

            if ($empleadoIds->isNotEmpty()) {
                // Si era docente, sus grupos quedan sin docente asignado (id_docente = NULL)
                // en lugar de borrar los grupos o las inscripciones de otros alumnos.
                $docenteIds = DB::table('docente')
                               ->whereIn('id_empleado', $empleadoIds)
                               ->pluck('id_docente');

                if ($docenteIds->isNotEmpty()) {
                    DB::table('grupo')
                      ->whereIn('id_docente', $docenteIds)
                      ->update(['id_docente' => null]);
                }

                DB::table('adscripcion')->whereIn('id_empleado', $empleadoIds)->delete();
                DB::table('docente')->whereIn('id_empleado', $empleadoIds)->delete();
                DB::table('empleado')->whereIn('id_empleado', $empleadoIds)->delete();
            }

            // ==================== 9. PERSONA ====================
            DB::table('persona')->where('id_persona', $idPersona)->delete();

            DB::commit();

            BitacoraService::registrar('DELETE', 'alumno', $id, [
                'id_persona'     => $idPersona,
                'numero_control' => $alumno->numero_control,
            ]);

            return response()->json(['message' => 'Alumno eliminado correctamente']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("ERROR DELETE ALUMNO ID {$id}: " . $e->getMessage());

            return response()->json([
                'error'   => 'No se pudo eliminar al alumno.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    /**
 * Retorna catálogos necesarios para el formulario de Alumnos
 */
    public function catalogos()
    {
        try {
            return response()->json([
                'generos' => DB::table('genero')->select('id_genero', 'nombre_genero')->get(),
                
                'carreras' => DB::table('carrera')
                    ->select('id_carrera', 'nombre')
                    ->where('estatus', true)
                    ->orderBy('nombre')
                    ->get(),
                
                'estatus_alumno' => DB::table('estatus_alumno')
                    ->select('id_estatus_alumno', 'nombre')
                    ->get(),
                
                'niveles' => DB::table('nivel_carrera')->get(), // por si lo necesitas después
            ]);
        } catch (\Exception $e) {
            Log::error("Error cargando catálogos de alumnos: " . $e->getMessage());
            return response()->json(['error' => 'Error al cargar catálogos'], 500);
        }
    }
}
