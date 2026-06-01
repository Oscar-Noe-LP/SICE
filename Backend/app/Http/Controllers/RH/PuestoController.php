<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PuestoController extends Controller
{
    /**
     * Obtener lista de puestos
     * 
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $puestos = DB::table('puesto')
                ->select('id_puesto', 'nombre_puesto', 'descripcion')
                ->orderBy('nombre_puesto', 'ASC')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $puestos,
                'total' => count($puestos),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener puestos: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener detalle de un puesto
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $puesto = DB::table('puesto')
                ->where('id_puesto', '=', $id)
                ->first();

            if (!$puesto) {
                return response()->json([
                    'success' => false,
                    'error' => 'Puesto no encontrado',
                ], 404);
            }

            // Contar empleados en este puesto
            $cantidadEmpleados = DB::table('empleado')
                ->where('id_puesto', '=', $id)
                ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'id_puesto' => $puesto->id_puesto,
                    'nombre_puesto' => $puesto->nombre_puesto,
                    'descripcion' => $puesto->descripcion,
                    'cantidad_empleados' => $cantidadEmpleados,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener puesto: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Crear nuevo puesto
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
{
    try {
        // 🔥 NORMALIZAR INPUT (AQUÍ VA)
        $input = $request->all();

        if (!isset($input['nombre_puesto']) && isset($input['nombre'])) {
            $input['nombre_puesto'] = $input['nombre'];
        }

        // Validar entrada
        $validator = Validator::make($input, [
            'nombre_puesto' => 'required|string|max:100|unique:puesto,nombre_puesto',
            'descripcion' => 'nullable|string|max:500',
        ], [
            'nombre_puesto.required' => 'El nombre del puesto es requerido',
            'nombre_puesto.unique' => 'El nombre del puesto ya existe',
            'nombre_puesto.max' => 'El nombre no puede exceder 100 caracteres',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Insertar puesto
        $idPuesto = DB::table('puesto')->insertGetId([
            'nombre_puesto' => $input['nombre_puesto'],
            'descripcion' => $input['descripcion'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Puesto creado exitosamente',
            'data' => [
                'id_puesto' => $idPuesto,
                'nombre_puesto' => $input['nombre_puesto'],
                'descripcion' => $input['descripcion'] ?? null,
            ],
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Error al crear puesto: ' . $e->getMessage(),
        ], 500);
    }
}
           

    /**
     * Actualizar puesto
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            // Validar que el puesto existe
            $puesto = DB::table('puesto')
                ->where('id_puesto', '=', $id)
                ->first();

            if (!$puesto) {
                return response()->json([
                    'success' => false,
                    'error' => 'Puesto no encontrado',
                ], 404);
            }

            // Validar entrada
            $validator = Validator::make($request->all(), [
                'nombre_puesto' => 'required|string|max:100|unique:puesto,nombre_puesto,' . $id . ',id_puesto',
                'descripcion' => 'nullable|string|max:500',
            ], [
                'nombre_puesto.required' => 'El nombre del puesto es requerido',
                'nombre_puesto.unique' => 'El nombre del puesto ya existe',
                'nombre_puesto.max' => 'El nombre no puede exceder 100 caracteres',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Actualizar puesto
            DB::table('puesto')
                ->where('id_puesto', '=', $id)
                ->update([
                    'nombre_puesto' => $request->get('nombre_puesto'),
                    'descripcion' => $request->get('descripcion'),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Puesto actualizado exitosamente',
                'data' => [
                    'id_puesto' => $id,
                    'nombre_puesto' => $request->get('nombre_puesto'),
                    'descripcion' => $request->get('descripcion'),
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al actualizar puesto: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Eliminar puesto
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            // Validar que el puesto existe
            $puesto = DB::table('puesto')
                ->where('id_puesto', '=', $id)
                ->first();

            if (!$puesto) {
                return response()->json([
                    'success' => false,
                    'error' => 'Puesto no encontrado',
                ], 404);
            }

            // Validar que no hay empleados en este puesto
            $empleadosCount = DB::table('empleado')
                ->where('id_puesto', '=', $id)
                ->count();

            if ($empleadosCount > 0) {
                return response()->json([
                    'success' => false,
                    'error' => "No se puede eliminar el puesto porque hay {$empleadosCount} empleado(s) asignado(s)",
                ], 409);
            }

            // Eliminar puesto
            DB::table('puesto')
                ->where('id_puesto', '=', $id)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Puesto eliminado exitosamente',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al eliminar puesto: ' . $e->getMessage(),
            ], 500);
        }
    }
}
