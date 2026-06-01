<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocentesController extends Controller
{
    // Lista de todos los docentes con datos de persona y empleado
    public function index()
    {
        try {
            $docentes = DB::table('docente')
                ->join('empleado', 'docente.id_empleado', '=', 'empleado.id_empleado')
                ->join('persona', 'empleado.id_persona', '=', 'persona.id_persona')
                ->join('puesto', 'empleado.id_puesto', '=', 'puesto.id_puesto')
                ->leftJoin('adscripcion', function ($join) {
                    $join->on('adscripcion.id_empleado', '=', 'empleado.id_empleado')
                         ->whereNull('adscripcion.fecha_fin');
                })
                ->leftJoin('departamento', 'adscripcion.id_departamento', '=', 'departamento.id_departamento')
                ->where('empleado.estatus', true)
                ->select(
                    'docente.id_docente',
                    'docente.grado_academico',
                    'docente.especialidad',
                    'empleado.id_empleado',
                    'empleado.numero_empleado',
                    'empleado.fecha_contratacion',
                    'persona.id_persona',
                    'persona.nombre',
                    'persona.apellido_paterno',
                    'persona.apellido_materno',
                    'persona.curp',
                    'puesto.nombre_puesto',
                    'departamento.nombre as departamento'
                )
                ->get();

            return response()->json($docentes);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'Error en servidor',
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

    // Actualizar datos de un docente (grado y especialidad)
    public function update(Request $request, $id_docente)
    {
        try {
            $request->validate([
                'grado_academico' => 'nullable|string|max:150',
                'especialidad'    => 'nullable|string|max:150',
            ]);

            $docente = DB::table('docente')->where('id_docente', $id_docente)->first();

            if (!$docente) {
                return response()->json(['error' => 'Docente no encontrado'], 404);
            }

            DB::table('docente')
                ->where('id_docente', $id_docente)
                ->update([
                    'grado_academico' => $request->input('grado_academico'),
                    'especialidad'    => $request->input('especialidad'),
                ]);

            return response()->json(['mensaje' => 'Docente actualizado correctamente']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error'  => 'Datos inválidos',
                'campos' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'Error en servidor',
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }
}
