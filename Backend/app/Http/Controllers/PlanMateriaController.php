<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlanMateriaController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'id_plan'    => 'required|integer|exists:plan_estudio,id_plan',
            'id_materia' => 'required|integer|exists:materia,id_materia',
            'semestre'   => 'required|integer|between:1,12',
        ]);

        // Evitar duplicados
        $existe = DB::table('plan_materia')
            ->where('id_plan', $request->id_plan)
            ->where('id_materia', $request->id_materia)
            ->exists();

        if ($existe) {
            return response()->json(['message' => 'Esta materia ya está en el plan'], 409);
        }

        DB::table('plan_materia')->insert([
            'id_plan'    => $request->id_plan,
            'id_materia' => $request->id_materia,
            'semestre'   => $request->semestre
        ]);

        return response()->json(['message' => 'Materia agregada al plan'], 201);
    }
}