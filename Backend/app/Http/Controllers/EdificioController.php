<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EdificioController extends Controller
{
    public function index()
    {
        return response()->json(
            DB::table('edificio')->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_edificio' => 'required|string|max:100'
        ]);

        DB::table('edificio')->insert([
            'nombre_edificio' => $request->nombre_edificio
        ]);

        return response()->json(['message' => 'Edificio creado'], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre_edificio' => 'required|string|max:100'
        ]);

        DB::table('edificio')
            ->where('id_edificio', $id)
            ->update([
                'nombre_edificio' => $request->nombre_edificio
            ]);

        return response()->json(['message' => 'Edificio actualizado']);
    }

    public function destroy($id)
    {
        DB::table('edificio')
            ->where('id_edificio', $id)
            ->delete();

        return response()->json(['message' => 'Edificio eliminado']);
    }
}