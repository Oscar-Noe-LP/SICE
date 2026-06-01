<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PersonaTelefonoController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'id_persona' => 'required|integer',
            'telefono' => 'required|string|max:20',
        ]);

        DB::statement("CALL InsertPersonaTelefono(?, ?)", [
            $request->id_persona,
            $request->telefono
        ]);

        return response()->json(['message' => 'Teléfono agregado correctamente']);
    }
}