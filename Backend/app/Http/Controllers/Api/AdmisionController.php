<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdmisionController extends Controller
{
    /**
     * GET /api/admision
     * Lista de aspirantes/admisiones
     */
    public function index(Request $request)
    {
        try {
            $query = DB::table('alumno as a')
                ->join('persona as p', 'a.id_persona', '=', 'p.id_persona')
                ->join('carrera as c', 'a.id_carrera', '=', 'c.id_carrera')
                ->whereIn('a.estatus', ['Aspirante', 'Preinscrito', 'Admitido'])
                ->select(
                    'a.id_alumno',
                    'a.numero_control',
                    DB::raw("CONCAT(p.nombre,' ',p.apellido_paterno,' ',COALESCE(p.apellido_materno,'')) as nombre_completo"),
                    'c.nombre as carrera',
                    'a.semestre_actual',
                    'a.estatus',
                    'p.curp',
                    'p.fecha_nacimiento'
                )
                ->orderBy('p.apellido_paterno');

            if ($request->filled('q')) {
                $q = $request->q;
                $query->where(function ($w) use ($q) {
                    $w->where('p.nombre', 'LIKE', "%$q%")
                      ->orWhere('p.apellido_paterno', 'LIKE', "%$q%")
                      ->orWhere('a.numero_control', 'LIKE', "%$q%");
                });
            }

            if ($request->filled('estatus')) {
                $query->where('a.estatus', $request->estatus);
            }

            return response()->json($query->get());

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/admision/{id}/cambiar-estatus
     * Cambia estatus de aspirante a admitido o rechazado
     */
    public function cambiarEstatus(Request $request, int $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'estatus' => 'required|string|in:Aspirante,Preinscrito,Admitido,Rechazado,Activo',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            $alumno = DB::table('alumno')->where('id_alumno', $id)->first();
            if (!$alumno) {
                return response()->json(['success' => false, 'error' => 'Alumno no encontrado'], 404);
            }

            $idEstatus = DB::table('estatus_alumno')
                ->where('nombre', $request->estatus)
                ->value('id_estatus_alumno');

            DB::table('alumno')->where('id_alumno', $id)->update([
                'estatus'           => $request->estatus,
                'id_estatus_alumno' => $idEstatus,  // sync FK
            ]);

            return response()->json([
                'success' => true,
                'message' => "Estatus actualizado a {$request->estatus}",
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
