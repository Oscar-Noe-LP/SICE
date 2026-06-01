<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartamentoController extends Controller
{
    public function index()
    {
        try {
            return response()->json(
                DB::table('departamento')
                    ->select('id_departamento', 'nombre', 'descripcion', 'estatus')
                    ->where('estatus', 1)
                    ->orderBy('nombre')
                    ->get()
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_departamento' => 'required|string|max:100|unique:departamento,nombre',
            'descripcion'         => 'nullable|string|max:255',
        ]);

        try {
            $id = DB::table('departamento')->insertGetId([
                'nombre'      => $request->nombre_departamento,
                'descripcion' => $request->descripcion,
                'estatus'     => 1,
            ]);

            $departamento = DB::table('departamento')->where('id_departamento', $id)->first();

            return response()->json([
                'message'      => 'Departamento creado correctamente',
                'departamento' => $departamento,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, int $id)
    {
        $dep = DB::table('departamento')->where('id_departamento', $id)->first();
        if (!$dep) {
            return response()->json(['error' => 'Departamento no encontrado'], 404);
        }

        $request->validate([
            'nombre_departamento' => 'required|string|max:100|unique:departamento,nombre,' . $id . ',id_departamento',
            'descripcion'         => 'nullable|string|max:255',
        ]);

        try {
            DB::table('departamento')->where('id_departamento', $id)->update([
                'nombre'      => $request->nombre_departamento,
                'descripcion' => $request->descripcion ?? $dep->descripcion,
            ]);

            return response()->json([
                'message'      => 'Departamento actualizado correctamente',
                'departamento' => DB::table('departamento')->where('id_departamento', $id)->first(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(int $id)
    {
        $dep = DB::table('departamento')->where('id_departamento', $id)->first();
        if (!$dep) {
            return response()->json(['error' => 'Departamento no encontrado'], 404);
        }

        try {
            // Baja lógica — no elimina físicamente para conservar historial
            DB::table('departamento')->where('id_departamento', $id)->update(['estatus' => 0]);

            return response()->json(['message' => 'Departamento eliminado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
