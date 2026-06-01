<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrerrequisitoController extends Controller
{
    /**
     * GET /api/prerrequisitos
     */
    public function index()
    {
        $datos = DB::table('prerrequisito as mp')
            ->join('materia as m', 'mp.id_materia', '=', 'm.id_materia')
            ->join('materia as p', 'mp.id_materia_prerrequisito', '=', 'p.id_materia')
            ->select(
                'mp.id_materia',
                'mp.id_materia_prerrequisito',
                DB::raw('JSON_OBJECT("nombre", m.nombre) as materia'),
                DB::raw('JSON_OBJECT("nombre", p.nombre) as prerrequisito')
            )
            ->get()
            ->map(function ($item) {
                $item->materia = json_decode($item->materia);
                $item->prerrequisito = json_decode($item->prerrequisito);
                return $item;
            });

        return response()->json($datos);
    }

    /**
     * POST /api/prerrequisitos
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_materia' => 'required|integer|exists:materia,id_materia',
            'id_materia_prerrequisito' => 'required|integer|exists:materia,id_materia',
        ]);

        // Evitar que sea su propio prerrequisito
        if ($request->id_materia == $request->id_materia_prerrequisito) {
            return response()->json([
                'message' => 'Una materia no puede ser su propio prerrequisito'
            ], 422);
        }

        // Evitar duplicados (esto también evita el error de doble click)
        $existe = DB::table('prerrequisito')
            ->where('id_materia', $request->id_materia)
            ->where('id_materia_prerrequisito', $request->id_materia_prerrequisito)
            ->exists();

        if ($existe) {
            return response()->json([
                'message' => 'Este prerrequisito ya está registrado'
            ], 409);
        }

        DB::table('prerrequisito')->insert([
            'id_materia' => $request->id_materia,
            'id_materia_prerrequisito' => $request->id_materia_prerrequisito
        ]);

        return response()->json([
            'message' => 'Prerrequisito creado correctamente'
        ], 201);
    }

    /**
     * DELETE /api/prerrequisitos/{id_materia}/{id_materia_prerrequisito}
     */
    public function destroy($id_materia, $id_materia_prerrequisito)
    {
        $deleted = DB::table('prerrequisito')
            ->where('id_materia', $id_materia)
            ->where('id_materia_prerrequisito', $id_materia_prerrequisito)
            ->delete();

        if (!$deleted) {
            return response()->json([
                'message' => 'Relación no encontrada'
            ], 404);
        }

        return response()->json([
            'message' => 'Prerrequisito eliminado correctamente'
        ]);
    }
}