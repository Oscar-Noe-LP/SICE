<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MateriaController extends Controller
{
    // ===============================
    // LISTAR
    // ===============================
    public function index()
    {
        return DB::table('materia')
            ->select(
                'id_materia',
                'clave',
                'nombre',
                'creditos',
                'horas_teoria',
                'horas_practica',
                'estatus'
            )
            ->get();
    }

    // ===============================
    // CREAR
    // ===============================
    public function store(Request $request)
    {
        $request->validate([
            'nombre'   => 'required|string|max:150',
            'creditos' => 'required|integer|min:0',
        ]);

        $id = DB::table('materia')->insertGetId([
            'clave'          => $request->clave,
            'nombre'         => $request->nombre,
            'descripcion'    => $request->descripcion,
            'creditos'       => $request->creditos,
            'horas_teoria'   => $request->horas_teoria   ?? 0,
            'horas_practica' => $request->horas_practica ?? 0,
            'estatus'        => $request->estatus ?? 1,
        ]);

        return response()->json(['message' => 'Materia creada', 'id_materia' => $id], 201);
    }

    // ===============================
    // ACTUALIZAR
    // ===============================
    public function update(Request $request, $id)
    {
        $materia = DB::table('materia')->where('id_materia', $id)->first();
        if (!$materia) {
            return response()->json(['error' => 'Materia no encontrada'], 404);
        }

        DB::table('materia')
            ->where('id_materia', $id)
            ->update([
                'nombre'         => $request->nombre          ?? $materia->nombre,
                'descripcion'    => $request->descripcion     ?? $materia->descripcion,
                'creditos'       => $request->creditos        ?? $materia->creditos,
                'horas_teoria'   => $request->horas_teoria    ?? $materia->horas_teoria,
                'horas_practica' => $request->horas_practica  ?? $materia->horas_practica,
                'estatus'        => $request->estatus         ?? $materia->estatus,
            ]);

        return response()->json(['message' => 'Materia actualizada']);
    }

    // ===============================
    // ELIMINAR
    // ===============================
    public function destroy($id)
    {
        DB::table('materia')
            ->where('id_materia', $id)
            ->delete();

        return response()->json(['message' => 'Materia eliminada']);
    }

    // ===============================
    // PLANES ASOCIADOS
    // ===============================
    public function planes($id)
    {
        $datos = DB::table('plan_materia as pm')
            ->join('plan_estudio as p', 'pm.id_plan', '=', 'p.id_plan')
            ->join('carrera as c', 'p.id_carrera', '=', 'c.id_carrera')
            ->where('pm.id_materia', $id)
            ->select(
                'pm.id_plan',
                'pm.semestre',
                'p.nombre_plan',
                'c.nombre as carrera_nombre'
            )
            ->get()
            ->map(function ($item) {
                return [
                    'id_plan' => $item->id_plan,
                    'semestre' => $item->semestre,
                    'plan' => [
                        'nombre_plan' => $item->nombre_plan,
                        'carrera' => [
                            'nombre' => $item->carrera_nombre
                        ]
                    ]
                ];
            });
    
        return response()->json($datos);
    }
}
