<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormularioEmpleadoController extends Controller
{
    // Buscar persona por nombre o CURP para vincular al empleado
    public function buscarPersona(Request $request)
    {
        try {
            $query = $request->input('q', '');

            $personas = DB::table('persona')
                ->where('estatus', true)
                ->where(function ($q) use ($query) {
                    $q->where('nombre', 'LIKE', "%{$query}%")
                      ->orWhere('apellido_paterno', 'LIKE', "%{$query}%")
                      ->orWhere('apellido_materno', 'LIKE', "%{$query}%")
                      ->orWhere('curp', 'LIKE', "%{$query}%");
                })
                ->select('id_persona', 'nombre', 'apellido_paterno', 'apellido_materno', 'curp')
                ->limit(20)
                ->get();

            return response()->json($personas);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'Error en servidor',
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

    // Crear un nuevo empleado (y opcionalmente marcarlo como docente)
    public function store(Request $request)
    {
        try {
            $request->validate([
                'id_persona'         => 'required|integer|exists:persona,id_persona',
                'numero_empleado'    => 'required|string|unique:empleado,numero_empleado',
                'id_puesto'          => 'required|integer|exists:puesto,id_puesto',
                'fecha_contratacion' => 'required|date',
                'id_departamento'    => 'nullable|integer|exists:departamento,id_departamento',
                // Campos opcionales para docente
                'es_docente'         => 'nullable|boolean',
                'grado_academico'    => 'nullable|string|max:150',
                'especialidad'       => 'nullable|string|max:150',
            ]);

            DB::beginTransaction();

            // 1. Crear empleado
            $idEmpleado = DB::table('empleado')->insertGetId([
                'id_persona'         => $request->id_persona,
                'numero_empleado'    => $request->numero_empleado,
                'id_puesto'          => $request->id_puesto,
                'fecha_contratacion' => $request->fecha_contratacion,
                'estatus'            => true,
            ]);

            // 2. Crear adscripcion al departamento (si se proporcionó)
            if ($request->filled('id_departamento')) {
                DB::table('adscripcion')->insert([
                    'id_empleado'     => $idEmpleado,
                    'id_departamento' => $request->id_departamento,
                    'fecha_inicio'    => $request->fecha_contratacion,
                ]);
            }

            // 3. Si es docente, crear registro en tabla docente
            if ($request->boolean('es_docente')) {
                DB::table('docente')->insert([
                    'id_empleado'     => $idEmpleado,
                    'grado_academico' => $request->input('grado_academico'),
                    'especialidad'    => $request->input('especialidad'),
                ]);
            }

            DB::commit();

            return response()->json([
                'mensaje'     => 'Empleado creado correctamente',
                'id_empleado' => $idEmpleado
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error'  => 'Datos inválidos',
                'campos' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error'   => 'Error en servidor',
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

    // Activar o desactivar el rol de docente de un empleado
    public function toggleDocente(Request $request, $id_empleado)
    {
        try {
            $empleado = DB::table('empleado')->where('id_empleado', $id_empleado)->first();

            if (!$empleado) {
                return response()->json(['error' => 'Empleado no encontrado'], 404);
            }

            $docente = DB::table('docente')->where('id_empleado', $id_empleado)->first();

            if ($docente) {
                // Ya es docente -> eliminar
                DB::table('docente')->where('id_empleado', $id_empleado)->delete();
                return response()->json(['mensaje' => 'Docente desactivado', 'es_docente' => false]);
            } else {
                // No es docente -> crear
                $request->validate([
                    'grado_academico' => 'nullable|string|max:150',
                    'especialidad'    => 'nullable|string|max:150',
                ]);

                DB::table('docente')->insert([
                    'id_empleado'     => $id_empleado,
                    'grado_academico' => $request->input('grado_academico'),
                    'especialidad'    => $request->input('especialidad'),
                ]);

                return response()->json(['mensaje' => 'Docente activado', 'es_docente' => true]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'Error en servidor',
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

    // Lista completa de empleados activos con datos de persona y puesto
    public function index()
    {
        try {
            $empleados = DB::table('empleado')
                ->join('persona', 'empleado.id_persona', '=', 'persona.id_persona')
                ->join('puesto', 'empleado.id_puesto', '=', 'puesto.id_puesto')
                ->leftJoin('adscripcion', function ($join) {
                    $join->on('adscripcion.id_empleado', '=', 'empleado.id_empleado')
                         ->whereNull('adscripcion.fecha_fin');
                })
                ->leftJoin('departamento', 'adscripcion.id_departamento', '=', 'departamento.id_departamento')
                ->where('empleado.estatus', true)
                ->select(
                    'empleado.id_empleado',
                    'empleado.numero_empleado',
                    'empleado.fecha_contratacion',
                    'persona.id_persona',
                    'persona.nombre',
                    'persona.apellido_paterno',
                    'persona.apellido_materno',
                    'persona.curp',
                    'puesto.id_puesto',
                    'puesto.nombre_puesto as puesto',
                    'departamento.id_departamento',
                    'departamento.nombre as departamento',
                    DB::raw('CONCAT(persona.nombre, " ", persona.apellido_paterno, IFNULL(CONCAT(" ", persona.apellido_materno), "")) as nombre'),
                    DB::raw('IF(empleado.estatus = 1, "Activo", "Inactivo") as estatus'),
                    DB::raw('EXISTS(SELECT 1 FROM docente d WHERE d.id_empleado = empleado.id_empleado) as es_docente')
                )
                ->get();

            return response()->json($empleados);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'Error en servidor',
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

    // Obtener un empleado por ID
    public function show($id)
    {
        try {
            $empleado = DB::table('empleado')
                ->join('persona', 'empleado.id_persona', '=', 'persona.id_persona')
                ->join('puesto', 'empleado.id_puesto', '=', 'puesto.id_puesto')
                ->leftJoin('adscripcion', function ($join) {
                    $join->on('adscripcion.id_empleado', '=', 'empleado.id_empleado')
                         ->whereNull('adscripcion.fecha_fin');
                })
                ->leftJoin('departamento', 'adscripcion.id_departamento', '=', 'departamento.id_departamento')
                ->where('empleado.id_empleado', $id)
                ->select(
                    'empleado.id_empleado',
                    'empleado.numero_empleado',
                    'empleado.fecha_contratacion',
                    'persona.id_persona',
                    'persona.nombre',
                    'persona.apellido_paterno',
                    'persona.apellido_materno',
                    'persona.curp',
                    'puesto.id_puesto',
                    'puesto.nombre_puesto as puesto',
                    'departamento.id_departamento',
                    'departamento.nombre as departamento',
                    DB::raw('CONCAT(persona.nombre, " ", persona.apellido_paterno, IFNULL(CONCAT(" ", persona.apellido_materno), "")) as nombre'),
                    DB::raw('IF(empleado.estatus = 1, "Activo", "Inactivo") as estatus'),
                    DB::raw('EXISTS(SELECT 1 FROM docente d WHERE d.id_empleado = empleado.id_empleado) as es_docente')
                )
                ->first();

            if (!$empleado) {
                return response()->json(['error' => 'Empleado no encontrado'], 404);
            }

            return response()->json($empleado);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'Error en servidor',
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

    // Actualizar datos de un empleado
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'numero_empleado'    => "nullable|string|unique:empleado,numero_empleado,{$id},id_empleado",
                'id_puesto'          => 'nullable|integer|exists:puesto,id_puesto',
                'fecha_contratacion' => 'nullable|date',
                'id_departamento'    => 'nullable|integer|exists:departamento,id_departamento',
                'estatus'            => 'nullable|boolean',
                'grado_academico'    => 'nullable|string|max:150',
                'especialidad'       => 'nullable|string|max:150',
            ]);

            $empleado = DB::table('empleado')->where('id_empleado', $id)->first();

            if (!$empleado) {
                return response()->json(['error' => 'Empleado no encontrado'], 404);
            }

            DB::beginTransaction();

            $campos = array_filter([
                'numero_empleado'    => $request->input('numero_empleado'),
                'id_puesto'          => $request->input('id_puesto'),
                'fecha_contratacion' => $request->input('fecha_contratacion'),
                'estatus'            => $request->input('estatus'),
            ], fn($v) => !is_null($v));

            if (!empty($campos)) {
                DB::table('empleado')->where('id_empleado', $id)->update($campos);
            }

            if ($request->filled('id_departamento')) {
                DB::table('adscripcion')
                    ->where('id_empleado', $id)
                    ->whereNull('fecha_fin')
                    ->update(['fecha_fin' => now()]);

                DB::table('adscripcion')->insert([
                    'id_empleado'     => $id,
                    'id_departamento' => $request->id_departamento,
                    'fecha_inicio'    => now(),
                ]);
            }

            DB::commit();

            return response()->json(['mensaje' => 'Empleado actualizado correctamente']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error'  => 'Datos inválidos',
                'campos' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error'   => 'Error en servidor',
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

    // Catálogos necesarios para el formulario
    public function catalogos()
    {
        return response()->json([
            'puestos'       => DB::table('puesto')->get(),
            'departamentos' => DB::table('departamento')->where('estatus', true)->get(),
        ]);
    }
}
