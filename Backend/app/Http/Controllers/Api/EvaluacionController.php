<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Evaluacion;
use Illuminate\Http\Request;

class EvaluacionController extends Controller
{
    // Listar todas las evaluaciones
    public function index()
    {
        return Evaluacion::all();
    }

    // Crear una nueva evaluación
    public function store(Request $request)
    {
        $request->validate([
            'id_grupo' => 'required|integer',
            'nombre' => 'required|string|max:50',
            'porcentaje' => 'required|numeric|min:0|max:100'
        ]);

        $evaluacion = Evaluacion::create($request->all());
        return response()->json($evaluacion, 201);
    }

    // Mostrar una evaluación específica
    public function show(string $id)
    {
        return Evaluacion::findOrFail($id);
    }

    // Actualizar una evaluación
    public function update(Request $request, string $id)
    {
        $request->validate([
            'id_grupo' => 'required|integer',
            'nombre' => 'required|string|max:50',
            'porcentaje' => 'required|numeric|min:0|max:100'
        ]);

        $evaluacion = Evaluacion::findOrFail($id);
        $evaluacion->update($request->all());
        return response()->json($evaluacion, 200);
    }

    // Eliminar una evaluación
    public function destroy(string $id)
    {
        Evaluacion::destroy($id);
        return response()->json(['message' => 'Eliminado'], 200);
    }
}