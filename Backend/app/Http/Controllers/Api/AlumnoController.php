<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alumno;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AlumnoController extends Controller
{
    public function index()
    {
        try {
            return Alumno::with(['persona', 'carrera'])->get();
        } catch (\Exception $e) {
            Log::error("Error index alumnos: " . $e->getMessage());
            return response()->json(['error' => 'Error al cargar alumnos'], 500);
        }
    }

    public function store(Request $request)
    {
        // Iniciamos transacción para que no se cree una persona si falla el alumno
        DB::beginTransaction();

        try {
            // 1. Validación de los datos que vienen de Vue
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

            // Mapeo simple para el id_genero de tu tabla persona
            $idGenero = match ($request->genero) {
                'Masculino' => 1,
                'Femenino'  => 2,
                default     => 3,
            };

            // 2. Crear la Persona
            $persona = Persona::create([
                'nombre'           => $request->nombre,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
                'id_genero'        => $idGenero,
                // Agrega campos por defecto para curp o fecha si son obligatorios en tu BD
                'curp'             => $request->curp ?? null,
                'fecha_nacimiento' => $request->fecha_nacimiento ?? null,
            ]);

            // 3. Crear el Alumno vinculado a esa persona
            $alumno = Alumno::create([
                'numero_control'   => $request->numero_control,
                'id_persona'       => $persona->id_persona,
                'id_carrera'       => $request->id_carrera,
                'semestre_actual'  => $request->semestre_actual,
                'estatus'          => $request->estatus,
                'fecha_ingreso'    => $request->fecha_ingreso,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Alumno registrado correctamente',
                'data' => $alumno->load('persona')
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Datos inválidos',
                'detalle' => $e->errors() 
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al crear alumno: " . $e->getMessage());
            return response()->json([
                'error' => 'Error interno del servidor',
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

            // 🔹 ACTUALIZAR PERSONA (nombre)
            if ($alumno->persona) {
                $alumno->persona->update([
                    'nombre' => $request->nombre ?? $alumno->persona->nombre,
                ]);
            }

            // 🔹 ACTUALIZAR ALUMNO
            $alumno->update([
                'id_carrera'      => $request->id_carrera ?? $alumno->id_carrera,
                'semestre_actual' => $request->semestre_actual ?? $alumno->semestre_actual,
                'estatus'         => $request->estatus ?? $alumno->estatus,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Alumno actualizado con éxito',
                'data' => $alumno->load(['persona', 'carrera'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("Error al actualizar alumno ID {$id}: " . $e->getMessage());

            return response()->json([
                'error' => 'No se pudo actualizar el alumno',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $alumno = Alumno::where('id_alumno', $id)->firstOrFail();
            $alumno->delete();

            return response()->json(['message' => 'Alumno eliminado con éxito']);
        } catch (\Exception $e) {
            Log::error("Error al eliminar alumno ID {$id}: " . $e->getMessage());
            return response()->json(['error' => 'No se pudo eliminar'], 500);
        }
    }
}
