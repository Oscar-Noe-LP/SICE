<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdscripcionController extends Controller
{
    /**
     * Obtener lista de adscripciones
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = DB::table('adscripcion as ad')
                ->join('empleado as e', 'ad.id_empleado', '=', 'e.id_empleado')
                ->join('persona as p', 'e.id_persona', '=', 'p.id_persona')
                ->join('departamento as d', 'ad.id_departamento', '=', 'd.id_departamento')
                ->select(
                    'ad.id_adscripcion',
                    'e.id_empleado',
                    'e.numero_empleado',
                    DB::raw("CONCAT(p.nombre, ' ', p.apellido_paterno, ' ', p.apellido_materno) as nombre_completo"),
                    'd.nombre as nombre_departamento',
                    'ad.fecha_inicio',
                    'ad.fecha_fin',
                    DB::raw('CASE WHEN ad.fecha_fin IS NULL OR ad.fecha_fin > CURDATE() THEN true ELSE false END as activa')
                );

            // Filtro por empleado
            if ($request->has('id_empleado') && !empty($request->get('id_empleado'))) {
                $query->where('e.id_empleado', '=', $request->get('id_empleado'));
            }

            // Filtro por departamento
            if ($request->has('id_departamento') && !empty($request->get('id_departamento'))) {
                $query->where('ad.id_departamento', '=', $request->get('id_departamento'));
            }

            // Filtro por estado (activa/inactiva)
            if ($request->has('activa') && $request->get('activa') !== '') {
                $activa = filter_var($request->get('activa'), FILTER_VALIDATE_BOOLEAN);
                if ($activa) {
                    $query->whereNull('ad.fecha_fin')
                        ->orWhere('ad.fecha_fin', '>', DB::raw('CURDATE()'));
                } else {
                    $query->whereNotNull('ad.fecha_fin')
                        ->where('ad.fecha_fin', '<=', DB::raw('CURDATE()'));
                }
            }

            $adscripciones = $query->orderBy('ad.fecha_inicio', 'DESC')
                ->get();

            // Transformar datos
            $adscripcionesFormateadas = $adscripciones->map(function ($adscripcion) {
                return [
                    'id_adscripcion' => $adscripcion->id_adscripcion,
                    'id_empleado' => $adscripcion->id_empleado,
                    'numero_empleado' => $adscripcion->numero_empleado,
                    'nombre_completo' => $adscripcion->nombre_completo,
                    'departamento' => $adscripcion->nombre_departamento,
                    'fecha_inicio' => $adscripcion->fecha_inicio,
                    'fecha_fin' => $adscripcion->fecha_fin,
                    'activa' => (bool) $adscripcion->activa,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $adscripcionesFormateadas,
                'total' => count($adscripcionesFormateadas),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener adscripciones: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener detalle de una adscripción
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $adscripcion = DB::table('adscripcion as ad')
                ->join('empleado as e', 'ad.id_empleado', '=', 'e.id_empleado')
                ->join('persona as p', 'e.id_persona', '=', 'p.id_persona')
                ->join('departamento as d', 'ad.id_departamento', '=', 'd.id_departamento')
                ->where('ad.id_adscripcion', '=', $id)
                ->select(
                    'ad.id_adscripcion',
                    'e.id_empleado',
                    'e.numero_empleado',
                    'p.nombre',
                    'p.apellido_paterno',
                    'p.apellido_materno',
                    'd.id_departamento',
                    'd.nombre as nombre_departamento',
                    'd.descripcion as descripcion_departamento',
                    'ad.fecha_inicio',
                    'ad.fecha_fin',
                    DB::raw('CASE WHEN ad.fecha_fin IS NULL OR ad.fecha_fin > CURDATE() THEN true ELSE false END as activa')
                )
                ->first();

            if (!$adscripcion) {
                return response()->json([
                    'success' => false,
                    'error' => 'Adscripción no encontrada',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id_adscripcion' => $adscripcion->id_adscripcion,
                    'empleado' => [
                        'id_empleado' => $adscripcion->id_empleado,
                        'numero_empleado' => $adscripcion->numero_empleado,
                        'nombre_completo' => "{$adscripcion->nombre} {$adscripcion->apellido_paterno} {$adscripcion->apellido_materno}",
                    ],
                    'departamento' => [
                        'id_departamento' => $adscripcion->id_departamento,
                        'nombre' => $adscripcion->nombre_departamento,
                        'descripcion' => $adscripcion->descripcion_departamento,
                    ],
                    'fecha_inicio' => $adscripcion->fecha_inicio,
                    'fecha_fin' => $adscripcion->fecha_fin,
                    'activa' => (bool) $adscripcion->activa,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener adscripción: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Crear nueva adscripción
     * Valida que no exista una adscripción activa para el empleado
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validar entrada
            $validator = Validator::make($request->all(), [
                'id_empleado' => 'required|integer|exists:empleado,id_empleado',
                'id_departamento' => 'required|integer|exists:departamento,id_departamento',
                'fecha_inicio' => 'required|date|date_format:Y-m-d',
                'fecha_fin' => 'nullable|date|date_format:Y-m-d|after:fecha_inicio',
            ], [
                'id_empleado.required' => 'El ID del empleado es requerido',
                'id_empleado.exists' => 'El empleado no existe',
                'id_departamento.required' => 'El ID del departamento es requerido',
                'id_departamento.exists' => 'El departamento no existe',
                'fecha_inicio.required' => 'La fecha de inicio es requerida',
                'fecha_inicio.date_format' => 'La fecha debe estar en formato Y-m-d',
                'fecha_fin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $idEmpleado = $request->get('id_empleado');

            // Validar adscripción activa
            $adscripcionActiva = DB::table('adscripcion')
                ->where('id_empleado', '=', $idEmpleado)
                ->where(function ($query) {
                    $query->whereNull('fecha_fin')
                        ->orWhere('fecha_fin', '>', DB::raw('CURDATE()'));
                })
                ->first();

            if ($adscripcionActiva) {
                return response()->json([
                    'success' => false,
                    'error' => 'El empleado ya tiene una adscripción activa',
                    'aviso_tipo' => 'warning',
                    'adscripcion_activa' => [
                        'id_adscripcion' => $adscripcionActiva->id_adscripcion,
                        'fecha_inicio' => $adscripcionActiva->fecha_inicio,
                        'fecha_fin' => $adscripcionActiva->fecha_fin,
                    ],
                ], 409);
            }

            // Crear adscripción
            $idAdscripcion = DB::table('adscripcion')->insertGetId([
                'id_empleado' => $idEmpleado,
                'id_departamento' => $request->get('id_departamento'),
                'fecha_inicio' => $request->get('fecha_inicio'),
                'fecha_fin' => $request->get('fecha_fin'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Adscripción creada exitosamente',
                'data' => [
                    'id_adscripcion' => $idAdscripcion,
                    'id_empleado' => $idEmpleado,
                    'id_departamento' => $request->get('id_departamento'),
                    'fecha_inicio' => $request->get('fecha_inicio'),
                    'fecha_fin' => $request->get('fecha_fin'),
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al crear adscripción: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualizar adscripción
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            // Validar que la adscripción existe
            $adscripcion = DB::table('adscripcion')
                ->where('id_adscripcion', '=', $id)
                ->first();

            if (!$adscripcion) {
                return response()->json([
                    'success' => false,
                    'error' => 'Adscripción no encontrada',
                ], 404);
            }

            // Validar entrada
            $validator = Validator::make($request->all(), [
                'id_departamento' => 'required|integer|exists:departamento,id_departamento',
                'fecha_inicio' => 'required|date|date_format:Y-m-d',
                'fecha_fin' => 'nullable|date|date_format:Y-m-d|after:fecha_inicio',
            ], [
                'id_departamento.required' => 'El ID del departamento es requerido',
                'id_departamento.exists' => 'El departamento no existe',
                'fecha_inicio.required' => 'La fecha de inicio es requerida',
                'fecha_fin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Actualizar adscripción
            DB::table('adscripcion')
                ->where('id_adscripcion', '=', $id)
                ->update([
                    'id_departamento' => $request->get('id_departamento'),
                    'fecha_inicio' => $request->get('fecha_inicio'),
                    'fecha_fin' => $request->get('fecha_fin'),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Adscripción actualizada exitosamente',
                'data' => [
                    'id_adscripcion' => $id,
                    'id_departamento' => $request->get('id_departamento'),
                    'fecha_inicio' => $request->get('fecha_inicio'),
                    'fecha_fin' => $request->get('fecha_fin'),
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al actualizar adscripción: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Eliminar adscripción
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            // Validar que la adscripción existe
            $adscripcion = DB::table('adscripcion')
                ->where('id_adscripcion', '=', $id)
                ->first();

            if (!$adscripcion) {
                return response()->json([
                    'success' => false,
                    'error' => 'Adscripción no encontrada',
                ], 404);
            }

            // Eliminar adscripción
            DB::table('adscripcion')
                ->where('id_adscripcion', '=', $id)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Adscripción eliminada exitosamente',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al eliminar adscripción: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener adscripción activa de un empleado
     * 
     * @param int $idEmpleado
     * @return JsonResponse
     */
    public function getAdscripcionActiva(int $idEmpleado): JsonResponse
    {
        try {
            $adscripcionActiva = DB::table('adscripcion as ad')
                ->join('departamento as d', 'ad.id_departamento', '=', 'd.id_departamento')
                ->where('ad.id_empleado', '=', $idEmpleado)
                ->where(function ($query) {
                    $query->whereNull('ad.fecha_fin')
                        ->orWhere('ad.fecha_fin', '>', DB::raw('CURDATE()'));
                })
                ->select(
                    'ad.id_adscripcion',
                    'ad.id_empleado',
                    'd.nombre as nombre_departamento',
                    'ad.fecha_inicio',
                    'ad.fecha_fin'
                )
                ->first();

            if (!$adscripcionActiva) {
                return response()->json([
                    'success' => true,
                    'data' => null,
                    'tiene_adscripcion_activa' => false,
                ], 200);
            }

            return response()->json([
                'success' => true,
                'data' => $adscripcionActiva,
                'tiene_adscripcion_activa' => true,
                'aviso_tipo' => 'warning',
                'mensaje' => 'El empleado ya tiene una adscripción activa',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al verificar adscripción activa: ' . $e->getMessage(),
            ], 500);
        }
    }
}
