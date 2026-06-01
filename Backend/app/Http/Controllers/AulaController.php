<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AulaController extends Controller
{
    public function index()
    {
        return response()->json(
            DB::table('aula')->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_edificio' => 'required|exists:edificio,id_edificio',
            'nombre'      => 'required|string|max:50',
            'capacidad'   => 'required|integer|min:1'
        ]);

        DB::table('aula')->insert([
            'id_edificio' => $request->id_edificio,
            'nombre'      => $request->nombre,
            'capacidad'   => $request->capacidad
        ]);

        return response()->json(['message' => 'Aula creada'], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id_edificio' => 'required|exists:edificio,id_edificio',
            'nombre'      => 'required|string|max:50',
            'capacidad'   => 'required|integer|min:1'
        ]);

        DB::table('aula')
            ->where('id_aula', $id)
            ->update([
                'id_edificio' => $request->id_edificio,
                'nombre'      => $request->nombre,
                'capacidad'   => $request->capacidad
            ]);

        return response()->json(['message' => 'Aula actualizada']);
    }

    public function destroy($id)
    {
        DB::table('aula')
            ->where('id_aula', $id)
            ->delete();

        return response()->json(['message' => 'Aula eliminada']);
    }
}