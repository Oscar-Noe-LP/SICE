<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PersonaController extends Controller
{
    /**
     * Catálogo completo de personas (con búsqueda opcional)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = DB::table('persona as pe')
                ->leftJoin('genero as g', 'pe.id_genero', '=', 'g.id_genero')
                ->where('pe.estatus', true)
                ->select(
                    'pe.id_persona',
                    'pe.nombre',
                    'pe.apellido_paterno',
                    'pe.apellido_materno',
                    'pe.curp',
                    'pe.fecha_nacimiento',
                    'g.nombre_genero as genero'
                )
                ->orderBy('pe.apellido_paterno')
                ->orderBy('pe.nombre');

            if ($request->filled('q')) {
                $q = $request->input('q');
                $query->where(function ($w) use ($q) {
                    $w->where('pe.nombre', 'LIKE', "%{$q}%")
                      ->orWhere('pe.apellido_paterno', 'LIKE', "%{$q}%")
                      ->orWhere('pe.apellido_materno', 'LIKE', "%{$q}%")
                      ->orWhere('pe.curp', 'LIKE', "%{$q}%");
                });
            }

            $personas = $query->get();

            return response()->json($personas, 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener detalle de una persona
     */
    public function show(int $id): JsonResponse
    {
        try {
            $persona = DB::table('persona as pe')
                ->leftJoin('genero as g', 'pe.id_genero', '=', 'g.id_genero')
                ->leftJoin('persona_correo as pc', 'pe.id_persona', '=', 'pc.id_persona')
                ->leftJoin('persona_telefono as pt', 'pe.id_persona', '=', 'pt.id_persona')
                ->leftJoin('persona_direccion as pd', 'pe.id_persona', '=', 'pd.id_persona')
                ->where('pe.id_persona', $id)
                ->select(
                    'pe.id_persona',
                    'pe.nombre',
                    'pe.apellido_paterno',
                    'pe.apellido_materno',
                    'pe.curp',
                    'pe.fecha_nacimiento',
                    'pe.id_genero',
                    'g.nombre_genero as genero',
                    'pc.correo',
                    'pt.telefono',
                    'pd.direccion'
                )
                ->first();

            if (!$persona) {
                return response()->json(['success' => false, 'error' => 'Persona no encontrada'], 404);
            }

            return response()->json(['success' => true, 'data' => $persona], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Crear nueva persona
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre'           => 'required|string|max:100',
                'apellido_paterno' => 'nullable|string|max:100',
                'apellido_materno' => 'nullable|string|max:100',
                'curp'             => 'nullable|string|max:18|unique:persona,curp',
                'fecha_nacimiento' => 'nullable|date',
                'id_genero'        => 'nullable|integer|exists:genero,id_genero',
                'correo'           => 'nullable|email|max:120',
                'telefono'         => 'nullable|string|max:20',
                'direccion'        => 'nullable|string',
            ], [
                'nombre.required'  => 'El nombre es requerido',
                'curp.unique'      => 'La CURP ya está registrada',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            DB::beginTransaction();

            $idPersona = DB::table('persona')->insertGetId([
                'nombre'           => $request->nombre,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
                'curp'             => $request->curp,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'id_genero'        => $request->id_genero,
            ]);

            if ($request->filled('correo')) {
                DB::table('persona_correo')->insert(['id_persona' => $idPersona, 'correo' => $request->correo]);
            }
            if ($request->filled('telefono')) {
                DB::table('persona_telefono')->insert(['id_persona' => $idPersona, 'telefono' => $request->telefono]);
            }
            if ($request->filled('direccion')) {
                DB::table('persona_direccion')->insert(['id_persona' => $idPersona, 'direccion' => $request->direccion]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Persona registrada exitosamente',
                'data'    => ['id_persona' => $idPersona],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Actualizar persona existente
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $persona = DB::table('persona')->where('id_persona', $id)->first();
            if (!$persona) {
                return response()->json(['success' => false, 'error' => 'Persona no encontrada'], 404);
            }

            $validator = Validator::make($request->all(), [
                'nombre'           => 'required|string|max:100',
                'apellido_paterno' => 'nullable|string|max:100',
                'apellido_materno' => 'nullable|string|max:100',
                'curp'             => 'nullable|string|max:18|unique:persona,curp,' . $id . ',id_persona',
                'fecha_nacimiento' => 'nullable|date',
                'id_genero'        => 'nullable|integer|exists:genero,id_genero',
                'correo'           => 'nullable|email|max:120',
                'telefono'         => 'nullable|string|max:20',
                'direccion'        => 'nullable|string',
            ], [
                'nombre.required' => 'El nombre es requerido',
                'curp.unique'     => 'La CURP ya está registrada',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            DB::beginTransaction();

            DB::table('persona')->where('id_persona', $id)->update([
                'nombre'           => $request->nombre,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
                'curp'             => $request->curp,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'id_genero'        => $request->id_genero,
            ]);

            // Correo: actualizar o insertar
            if ($request->has('correo')) {
                $existe = DB::table('persona_correo')->where('id_persona', $id)->exists();
                if ($existe) {
                    DB::table('persona_correo')->where('id_persona', $id)->update(['correo' => $request->correo]);
                } elseif ($request->filled('correo')) {
                    DB::table('persona_correo')->insert(['id_persona' => $id, 'correo' => $request->correo]);
                }
            }

            // Teléfono
            if ($request->has('telefono')) {
                $existe = DB::table('persona_telefono')->where('id_persona', $id)->exists();
                if ($existe) {
                    DB::table('persona_telefono')->where('id_persona', $id)->update(['telefono' => $request->telefono]);
                } elseif ($request->filled('telefono')) {
                    DB::table('persona_telefono')->insert(['id_persona' => $id, 'telefono' => $request->telefono]);
                }
            }

            // Dirección
            if ($request->has('direccion')) {
                $existe = DB::table('persona_direccion')->where('id_persona', $id)->exists();
                if ($existe) {
                    DB::table('persona_direccion')->where('id_persona', $id)->update(['direccion' => $request->direccion]);
                } elseif ($request->filled('direccion')) {
                    DB::table('persona_direccion')->insert(['id_persona' => $id, 'direccion' => $request->direccion]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Persona actualizada exitosamente',
                'data'    => ['id_persona' => $id],
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
