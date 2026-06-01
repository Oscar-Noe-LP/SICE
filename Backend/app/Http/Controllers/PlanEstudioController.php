<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlanEstudioController extends Controller
{
    // ─────────────────────────────────────────────
    // LISTADO (el que usa tu Vue)
    public function index()
    {
        $planes = DB::table('plan_estudio as p')
            ->leftJoin('carrera as c', 'p.id_carrera', '=', 'c.id_carrera')
            ->select(
                'p.id_plan',
                'p.id_carrera',
                'p.nombre_plan',
                'p.anio_vigencia',
                'p.total_creditos',
                'p.estatus',
                'c.nombre as carrera_nombre'
            )
            ->orderBy('p.anio_vigencia', 'desc')
            ->get()
            ->map(function ($p) {
                return [
                    'id_plan' => $p->id_plan,
                    'id_carrera' => $p->id_carrera,
                    'nombre_plan' => $p->nombre_plan,
                    'anio_vigencia' => $p->anio_vigencia,
                    'total_creditos' => $p->total_creditos,
                    'estatus' => $p->estatus,
                    'carrera' => [
                        'nombre' => $p->carrera_nombre
                    ]
                ];
            });

        return response()->json($planes);
    }

    // ─────────────────────────────────────────────
    // CREAR
    public function store(Request $request)
    {
        $request->validate([
            'id_carrera'      => 'required|exists:carrera,id_carrera',
            'nombre_plan'     => 'required|string|max:100',
            'anio_vigencia'   => 'required|integer|min:2000|max:2099',
            'total_creditos'  => 'required|integer|min:1',
            'estatus'         => 'required|boolean'
        ]);

        DB::table('plan_estudio')->insert([
            'id_carrera'     => $request->id_carrera,
            'nombre_plan'    => $request->nombre_plan,
            'anio_vigencia'  => $request->anio_vigencia,
            'total_creditos' => $request->total_creditos,
            'estatus'        => $request->estatus
        ]);

        return response()->json(['message' => 'Plan creado']);
    }

    // ─────────────────────────────────────────────
    // ACTUALIZAR
    public function update(Request $request, $id)
    {
        $request->validate([
            'id_carrera'      => 'required|exists:carrera,id_carrera',
            'nombre_plan'     => 'required|string|max:100',
            'anio_vigencia'   => 'required|integer|min:2000|max:2099',
            'total_creditos'  => 'required|integer|min:1',
            'estatus'         => 'required|boolean'
        ]);

        DB::table('plan_estudio')
            ->where('id_plan', $id)
            ->update([
                'id_carrera'     => $request->id_carrera,
                'nombre_plan'    => $request->nombre_plan,
                'anio_vigencia'  => $request->anio_vigencia,
                'total_creditos' => $request->total_creditos,
                'estatus'        => $request->estatus
            ]);

        return response()->json(['message' => 'Plan actualizado']);
    }

    // ─────────────────────────────────────────────
    // ELIMINAR
    public function destroy($id)
    {
        DB::table('plan_estudio')
            ->where('id_plan', $id)
            ->delete();

        return response()->json(['message' => 'Plan eliminado']);
    }
}
