<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EspecialidadController extends Controller
{
    public function index(Request $request)
    {
        try {
            $q = DB::table('especialidad as e')
                ->join('carrera as c', 'e.id_carrera', '=', 'c.id_carrera')
                ->select('e.id_especialidad', 'e.id_carrera', 'c.nombre as carrera',
                         'e.nombre', 'e.descripcion', 'e.estatus');

            if ($request->filled('id_carrera')) {
                $q->where('e.id_carrera', $request->id_carrera);
            }
            if ($request->filled('estatus')) {
                $q->where('e.estatus', $request->estatus);
            }

            return response()->json($q->orderBy('e.nombre')->get());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $esp = DB::table('especialidad as e')
            ->join('carrera as c', 'e.id_carrera', '=', 'c.id_carrera')
            ->where('e.id_especialidad', $id)
            ->select('e.*', 'c.nombre as carrera')
            ->first();

        if (!$esp) return response()->json(['error' => 'Especialidad no encontrada'], 404);
        return response()->json($esp);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_carrera' => 'required|integer|exists:carrera,id_carrera',
            'nombre'     => 'required|string|max:150',
            'descripcion'=> 'nullable|string',
        ]);

        $id = DB::table('especialidad')->insertGetId([
            'id_carrera'  => $request->id_carrera,
            'nombre'      => $request->nombre,
            'descripcion' => $request->descripcion,
            'estatus'     => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return response()->json(['mensaje' => 'Especialidad creada', 'id_especialidad' => $id], 201);
    }

    public function update(Request $request, $id)
    {
        $esp = DB::table('especialidad')->where('id_especialidad', $id)->first();
        if (!$esp) return response()->json(['error' => 'Especialidad no encontrada'], 404);

        $request->validate([
            'nombre'     => 'sometimes|string|max:150',
            'descripcion'=> 'sometimes|nullable|string',
            'estatus'    => 'sometimes|boolean',
            'id_carrera' => 'sometimes|integer|exists:carrera,id_carrera',
        ]);

        DB::table('especialidad')->where('id_especialidad', $id)->update(array_filter([
            'nombre'      => $request->nombre,
            'descripcion' => $request->descripcion,
            'estatus'     => $request->has('estatus') ? $request->estatus : null,
            'id_carrera'  => $request->id_carrera,
            'updated_at'  => now(),
        ], fn($v) => $v !== null));

        return response()->json(['mensaje' => 'Especialidad actualizada']);
    }

    public function destroy($id)
    {
        $esp = DB::table('especialidad')->where('id_especialidad', $id)->first();
        if (!$esp) return response()->json(['error' => 'Especialidad no encontrada'], 404);

        DB::table('especialidad')->where('id_especialidad', $id)->update(['estatus' => 0, 'updated_at' => now()]);
        return response()->json(['mensaje' => 'Especialidad desactivada']);
    }
}
