<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class EmpleadoController extends Controller
{
    /**
     * Obtener lista de empleados con filtros
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = DB::table('empleado as e')
                ->join('persona as p', 'e.id_persona', '=', 'p.id_persona')
                ->join('puesto as pu', 'e.id_puesto', '=', 'pu.id_puesto')
                ->leftJoin('docente as d', 'e.id_empleado', '=', 'd.id_empleado')
                ->leftJoin('adscripcion as ad', function ($join) {
                    $join->on('ad.id_empleado', '=', 'e.id_empleado')
                         ->whereNull('ad.fecha_fin');
                })
                ->leftJoin('departamento as dp', 'ad.id_departamento', '=', 'dp.id_departamento');

            // Filtro por nombre
            if ($request->has('nombre') && !empty($request->get('nombre'))) {
                $nombre = $request->get('nombre');
                $query->where(DB::raw("CONCAT(p.nombre, ' ', p.apellido_paterno, ' ', p.apellido_materno)"), 'LIKE', "%{$nombre}%");
            }

            // Filtro por número de empleado
            if ($request->has('numero_empleado') && !empty($request->get('numero_empleado'))) {
                $query->where('e.numero_empleado', '=', $request->get('numero_empleado'));
            }

            // Filtro por puesto
            if ($request->has('id_puesto') && !empty($request->get('id_puesto'))) {
                $query->where('e.id_puesto', '=', $request->get('id_puesto'));
            }

            // Filtro por estatus
            if ($request->has('estatus') && $request->get('estatus') !== '') {
                $estatus = filter_var($request->get('estatus'), FILTER_VALIDATE_BOOLEAN);
                $query->where('e.estatus', '=', $estatus);
            }

            $empleados = $query->select(
                'e.id_empleado',
                'e.numero_empleado',
                'p.nombre',
                'p.apellido_paterno',
                'p.apellido_materno',
                'pu.nombre_puesto',
                'e.fecha_contratacion',
                'e.estatus',
                'dp.nombre as nombre_departamento',
                DB::raw('CASE WHEN d.id_docente IS NOT NULL THEN true ELSE false END as es_docente')
            )
            ->distinct()
            ->orderBy('e.numero_empleado', 'ASC')
            ->get();

            // Transformar datos para respuesta
            $empleadosFormateados = $empleados->map(function ($empleado) {
                return [
                    'id_empleado' => $empleado->id_empleado,
                    'numero_empleado' => $empleado->numero_empleado,
                    'nombre_completo' => "{$empleado->nombre} {$empleado->apellido_paterno} {$empleado->apellido_materno}",
                    'puesto' => $empleado->nombre_puesto,
                    'departamento' => $empleado->nombre_departamento,
                    'fecha_contratacion' => $empleado->fecha_contratacion,
                    'estatus' => $empleado->estatus,
                    'es_docente' => (bool) $empleado->es_docente,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $empleadosFormateados,
                'total' => count($empleadosFormateados),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener empleados: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener detalles completos de un empleado
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $empleado = DB::table('empleado as e')
                ->join('persona as p', 'e.id_persona', '=', 'p.id_persona')
                ->join('puesto as pu', 'e.id_puesto', '=', 'pu.id_puesto')
                ->leftJoin('docente as d', 'e.id_empleado', '=', 'd.id_empleado')
                ->leftJoin('adscripcion as ad', 'e.id_empleado', '=', 'ad.id_empleado')
                ->leftJoin('departamento as dp', 'ad.id_departamento', '=', 'dp.id_departamento')
                ->where('e.id_empleado', '=', $id)
                ->select(
                    'e.id_empleado',
                    'e.numero_empleado',
                    'p.nombre',
                    'p.apellido_paterno',
                    'p.apellido_materno',
                    'p.curp',
                    'p.fecha_nacimiento',
                    'pu.id_puesto',
                    'pu.nombre_puesto',
                    'pu.descripcion as descripcion_puesto',
                    'e.fecha_contratacion',
                    'e.estatus',
                    'd.id_docente',
                    'd.grado_academico',
                    'd.especialidad',
                    'ad.id_adscripcion',
                    'ad.id_departamento',
                    'dp.nombre as nombre_departamento',
                    'ad.fecha_inicio as fecha_inicio_adscripcion',
                    'ad.fecha_fin as fecha_fin_adscripcion'
                )
                ->first();

            if (!$empleado) {
                return response()->json([
                    'success' => false,
                    'error' => 'Empleado no encontrado',
                ], 404);
            }

            // Validar adscripción activa
            $adscripcionActiva = DB::table('adscripcion')
                ->where('id_empleado', '=', $id)
                ->whereNull('fecha_fin')
                ->orWhere('fecha_fin', '>', now())
                ->first();

            $empleadoFormateado = [
                'id_empleado' => $empleado->id_empleado,
                'numero_empleado' => $empleado->numero_empleado,
                'nombre' => $empleado->nombre,
                'apellido_paterno' => $empleado->apellido_paterno,
                'apellido_materno' => $empleado->apellido_materno,
                'nombre_completo' => "{$empleado->nombre} {$empleado->apellido_paterno} {$empleado->apellido_materno}",
                'curp' => $empleado->curp,
                'fecha_nacimiento' => $empleado->fecha_nacimiento,
                'puesto' => [
                    'id_puesto' => $empleado->id_puesto,
                    'nombre' => $empleado->nombre_puesto,
                    'descripcion' => $empleado->descripcion_puesto,
                ],
                'fecha_contratacion' => $empleado->fecha_contratacion,
                'estatus' => (bool) $empleado->estatus,
                'es_docente' => !is_null($empleado->id_docente),
                'datos_docente' => is_null($empleado->id_docente) ? null : [
                    'grado_academico' => $empleado->grado_academico,
                    'especialidad' => $empleado->especialidad,
                ],
                'adscripcion_activa' => !is_null($adscripcionActiva),
                'adscripcion' => is_null($empleado->id_adscripcion) ? null : [
                    'id_adscripcion' => $empleado->id_adscripcion,
                    'departamento' => $empleado->nombre_departamento,
                    'fecha_inicio' => $empleado->fecha_inicio_adscripcion,
                    'fecha_fin' => $empleado->fecha_fin_adscripcion,
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $empleadoFormateado,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener empleado: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener lista de departamentos para filtros
     * 
     * @return JsonResponse
     */
    public function getDepartamentos(): JsonResponse
    {
        try {
            $departamentos = DB::table('departamento')
                ->where('estatus', '=', true)
                ->select('id_departamento', 'nombre')
                ->orderBy('nombre', 'ASC')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $departamentos,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener departamentos: ' . $e->getMessage(),
            ], 500);
        }
    }
}
