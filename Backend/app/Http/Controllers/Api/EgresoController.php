<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EgresoController extends Controller
{
    /**
     * GET /api/egresos
     * Lista de alumnos en proceso de egreso o ya egresados
     */
    public function index(Request $request)
    {
        try {
            $query = DB::table('alumno as a')
                ->join('persona as p', 'a.id_persona', '=', 'p.id_persona')
                ->join('carrera as c', 'a.id_carrera', '=', 'c.id_carrera')
                ->whereIn('a.estatus', ['Egresado', 'Titulado', 'Proceso de Egreso'])
                ->select(
                    'a.id_alumno',
                    'a.numero_control',
                    DB::raw("CONCAT(p.nombre,' ',p.apellido_paterno,' ',COALESCE(p.apellido_materno,'')) as nombre_completo"),
                    'c.nombre as carrera',
                    'a.semestre_actual',
                    'a.estatus'
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

            return response()->json($query->get());

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/egresos
     * Iniciar proceso de egreso de un alumno
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_alumno' => 'required|integer|exists:alumno,id_alumno',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            $alumno = DB::table('alumno')->where('id_alumno', $request->id_alumno)->first();

            // Validar que sea alumno activo
            if ($alumno->estatus !== 'Activo') {
                return response()->json([
                    'success' => false,
                    'error'   => 'Solo se puede iniciar egreso para alumnos con estatus Activo',
                ], 422);
            }

            // Verificar créditos completados
            $creditosAcum = DB::table('inscripcion as i')
                ->join('grupo as g', 'i.id_grupo', '=', 'g.id_grupo')
                ->join('materia as m', 'g.id_materia', '=', 'm.id_materia')
                ->join('calificacion as cal', 'cal.id_inscripcion', '=', 'i.id_inscripcion')
                ->where('i.id_alumno', $request->id_alumno)
                ->where('cal.calificacion', '>=', 70)
                ->sum('m.creditos');

            DB::table('alumno')->where('id_alumno', $request->id_alumno)->update([
                'estatus' => 'Proceso de Egreso',
            ]);

            return response()->json([
                'success'           => true,
                'message'           => 'Proceso de egreso iniciado',
                'creditos_acumulados' => $creditosAcum,
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * PUT /api/egresos/{id}/titular
     * Marcar alumno como Titulado
     */
    public function titular(int $id)
    {
        try {
            $alumno = DB::table('alumno')->where('id_alumno', $id)->first();
            if (!$alumno) {
                return response()->json(['success' => false, 'error' => 'Alumno no encontrado'], 404);
            }

            DB::table('alumno')->where('id_alumno', $id)->update(['estatus' => 'Titulado']);

            return response()->json(['success' => true, 'message' => 'Alumno marcado como Titulado']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
