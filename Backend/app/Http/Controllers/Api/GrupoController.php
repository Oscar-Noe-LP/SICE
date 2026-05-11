<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GrupoController extends Controller
{
    public function index()
    {
        try {
            $grupos = DB::table('grupo as g')
                ->leftJoin('materia as m',    'g.id_materia', '=', 'm.id_materia')
                ->leftJoin('docente as d',    'g.id_docente', '=', 'd.id_docente')
                ->leftJoin('empleado as e',   'd.id_empleado','=', 'e.id_empleado')
                ->leftJoin('persona as p',    'e.id_persona', '=', 'p.id_persona')
                ->leftJoin('aula as a',       'g.id_aula',    '=', 'a.id_aula')
                ->leftJoin('inscripcion as i','g.id_grupo',   '=', 'i.id_grupo')
                ->select(
                    'g.id_grupo',
                    'g.clave_grupo',
                    'm.nombre as materia',
                    'm.id_materia',
                    DB::raw("COALESCE(CONCAT(p.nombre, ' ', p.apellido_paterno), 'Sin docente') as docente"),
                    'a.nombre as aula',
                    'g.capacidad',
                    'g.dia',
                    'g.hora_inicio',
                    'g.hora_fin',
                    'g.id_periodo',
                    DB::raw("COUNT(CASE WHEN i.estatus IN ('Activo','activo','inscrito') THEN 1 END) as inscritos")
                )
                ->groupBy(
                    'g.id_grupo', 'g.clave_grupo', 'm.nombre', 'm.id_materia',
                    'p.nombre', 'p.apellido_paterno', 'a.nombre', 'g.capacidad',
                    'g.dia', 'g.hora_inicio', 'g.hora_fin', 'g.id_periodo'
                )
                ->get();

            return response()->json($grupos);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'clave_grupo'    => 'nullable|string|max:20|unique:grupo,clave_grupo',
                'nombre_materia' => 'required|string',
                'aula'           => 'required|string',
                'nombre_docente' => 'nullable|string',
                'capacidad'      => 'required|integer|min:1',
                'id_periodo'     => 'nullable|integer|exists:periodo,id_periodo',
                'dia'            => 'nullable|string|max:50',
                'hora_inicio'    => 'nullable|date_format:H:i',
                'hora_fin'       => 'nullable|date_format:H:i',
            ], [
                'clave_grupo.unique'      => 'Ya existe un grupo con esa clave',
                'nombre_materia.required' => 'La materia es requerida',
                'aula.required'           => 'El aula es requerida',
                'capacidad.required'      => 'La capacidad es requerida',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            $id_materia = DB::table('materia')->where('nombre', $request->nombre_materia)->value('id_materia');
            if (!$id_materia) {
                return response()->json(['success' => false, 'error' => 'La materia indicada no existe'], 422);
            }

            $id_aula = DB::table('aula')->where('nombre', $request->aula)->value('id_aula');
            if (!$id_aula) {
                return response()->json(['success' => false, 'error' => 'El aula indicada no existe'], 422);
            }

            $id_docente = null;
            if ($request->filled('nombre_docente')) {
                $id_docente = DB::table('docente as d')
                    ->join('empleado as e', 'd.id_empleado', '=', 'e.id_empleado')
                    ->join('persona as p',  'e.id_persona',  '=', 'p.id_persona')
                    ->where(DB::raw("TRIM(CONCAT(p.nombre, ' ', p.apellido_paterno, ' ', COALESCE(p.apellido_materno, '')))"), 'like', $request->nombre_docente . '%')
                    ->value('d.id_docente');
                if (!$id_docente) {
                    return response()->json(['success' => false, 'error' => 'El docente indicado no existe'], 422);
                }
            }

            $id_periodo = $request->id_periodo
                ?? DB::table('periodo')->where('estatus', 1)->orderByDesc('id_periodo')->value('id_periodo');

            $clave_grupo = $request->clave_grupo
                ?? strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $request->nombre_materia), 0, 6))
                   . '-' . now()->format('mdHi');

            $id = DB::table('grupo')->insertGetId([
                'clave_grupo' => $clave_grupo,
                'id_materia'  => $id_materia,
                'id_docente'  => $id_docente,
                'id_aula'     => $id_aula,
                'id_periodo'  => $id_periodo,
                'capacidad'   => $request->capacidad,
                'dia'         => $request->dia,
                'hora_inicio' => $request->hora_inicio,
                'hora_fin'    => $request->hora_fin,
                'estatus'     => 1,
            ]);

            BitacoraService::registrar('INSERT', 'grupo', $id, [], [
                'clave_grupo' => $clave_grupo,
                'id_materia'  => $id_materia,
                'id_docente'  => $id_docente,
                'capacidad'   => $request->capacidad,
            ]);

            return response()->json(['success' => true, 'message' => 'Grupo creado', 'id' => $id], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $grupo = DB::table('grupo')->where('id_grupo', $id)->first();
            if (!$grupo) {
                return response()->json(['success' => false, 'error' => 'Grupo no encontrado'], 404);
            }

            $validator = Validator::make($request->all(), [
                'clave_grupo'    => 'sometimes|string|max:20|unique:grupo,clave_grupo,' . $id . ',id_grupo',
                'nombre_materia' => 'sometimes|string',
                'aula'           => 'sometimes|string',
                'nombre_docente' => 'nullable|string',
                'capacidad'      => 'sometimes|integer|min:1',
                'id_periodo'     => 'sometimes|nullable|integer|exists:periodo,id_periodo',
                'dia'            => 'sometimes|nullable|string|max:50',
                'hora_inicio'    => 'sometimes|nullable|date_format:H:i',
                'hora_fin'       => 'sometimes|nullable|date_format:H:i',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            $id_materia = $grupo->id_materia;
            if ($request->filled('nombre_materia')) {
                $id_materia = DB::table('materia')->where('nombre', $request->nombre_materia)->value('id_materia');
                if (!$id_materia) {
                    return response()->json(['success' => false, 'error' => 'La materia indicada no existe'], 422);
                }
            }

            $id_aula = $grupo->id_aula;
            if ($request->filled('aula')) {
                $id_aula = DB::table('aula')->where('nombre', $request->aula)->value('id_aula');
                if (!$id_aula) {
                    return response()->json(['success' => false, 'error' => 'El aula indicada no existe'], 422);
                }
            }

            $id_docente = $grupo->id_docente;
            if ($request->has('nombre_docente')) {
                if ($request->filled('nombre_docente')) {
                    $id_docente = DB::table('docente as d')
                        ->join('empleado as e', 'd.id_empleado', '=', 'e.id_empleado')
                        ->join('persona as p',  'e.id_persona',  '=', 'p.id_persona')
                        ->where(DB::raw("TRIM(CONCAT(p.nombre, ' ', p.apellido_paterno, ' ', COALESCE(p.apellido_materno, '')))"), 'like', $request->nombre_docente . '%')
                        ->value('d.id_docente');
                    if (!$id_docente) {
                        return response()->json(['success' => false, 'error' => 'El docente indicado no existe'], 422);
                    }
                } else {
                    $id_docente = null;
                }
            }

            DB::table('grupo')->where('id_grupo', $id)->update([
                'clave_grupo' => $request->clave_grupo ?? $grupo->clave_grupo,
                'id_materia'  => $id_materia,
                'id_aula'     => $id_aula,
                'id_docente'  => $id_docente,
                'id_periodo'  => $request->id_periodo ?? $grupo->id_periodo,
                'capacidad'   => $request->capacidad  ?? $grupo->capacidad,
                'dia'         => $request->has('dia')         ? $request->dia         : $grupo->dia,
                'hora_inicio' => $request->has('hora_inicio') ? $request->hora_inicio : $grupo->hora_inicio,
                'hora_fin'    => $request->has('hora_fin')    ? $request->hora_fin    : $grupo->hora_fin,
            ]);

            BitacoraService::registrar('UPDATE', 'grupo', $id, (array) $grupo, $request->only([
                'clave_grupo', 'capacidad', 'dia', 'hora_inicio', 'hora_fin',
            ]));

            return response()->json(['success' => true, 'message' => 'Grupo actualizado']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $grupo = DB::table('grupo')->where('id_grupo', $id)->first();
            if (!$grupo) {
                return response()->json(['success' => false, 'error' => 'Grupo no encontrado'], 404);
            }

            $inscritos = DB::table('inscripcion')
                ->where('id_grupo', $id)
                ->whereIn('estatus', ['Activo', 'activo', 'inscrito'])
                ->count();

            if ($inscritos > 0) {
                return response()->json([
                    'success' => false,
                    'error'   => 'No se puede eliminar el grupo porque tiene alumnos inscritos'
                ], 409);
            }

            DB::table('grupo')->where('id_grupo', $id)->delete();
            BitacoraService::registrar('DELETE', 'grupo', $id, (array) $grupo);
            return response()->json(['success' => true, 'message' => 'Grupo eliminado']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // Deshabilitados hasta que la BD tenga las columnas acta_cerrada y fecha_cierre_acta
    public function cerrarActa(int $id)
    {
        return response()->json([
            'success' => false,
            'error'   => 'Funcionalidad no disponible en esta versión de la BD'
        ], 503);
    }

    public function abrirActa(int $id)
    {
        return response()->json([
            'success' => false,
            'error'   => 'Funcionalidad no disponible en esta versión de la BD'
        ], 503);
    }
}