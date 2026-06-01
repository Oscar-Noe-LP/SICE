<?php

namespace App\Http\Controllers\Docentes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AsignacionDocenteController extends Controller
{
    /**
     * GET /api/asignacion-docente/grupos
     * Devuelve exactamente lo que espera tu frontend
     */
    public function grupos()
    {
        try {
            $grupos = DB::table('grupo as g')
                ->join('materia as m', 'g.id_materia', '=', 'm.id_materia')
                ->leftJoin('plan_materia as pm', 'm.id_materia', '=', 'pm.id_materia')
                ->leftJoin('plan_estudio as pe', 'pm.id_plan', '=', 'pe.id_plan')
                ->leftJoin('carrera as c', 'pe.id_carrera', '=', 'c.id_carrera')
                ->leftJoin('periodo as p', 'g.id_periodo', '=', 'p.id_periodo')
                ->leftJoin('docente as d', 'g.id_docente', '=', 'd.id_docente')
                ->leftJoin('empleado as e', 'd.id_empleado', '=', 'e.id_empleado')
                ->leftJoin('persona as per', 'e.id_persona', '=', 'per.id_persona')
                ->select(
                    'g.id_grupo',
                    'g.clave_grupo',
                    'm.nombre as materia',
                    DB::raw("COALESCE(c.nombre, 'Sin carrera') as carrera"),
                    DB::raw('COALESCE(pm.semestre, 0) as semestre'),
                    DB::raw("NULL as hora_inicio"),
                    DB::raw("NULL as hora_fin"),
                    DB::raw("NULL as dia"),
                    'g.capacidad',
                    DB::raw('(SELECT COUNT(*) FROM inscripcion i WHERE i.id_grupo = g.id_grupo) as inscritos'),
                    DB::raw("CASE WHEN g.id_docente IS NOT NULL THEN 'Con docente' ELSE 'Sin docente' END as estado"),
                    DB::raw("CASE WHEN per.nombre IS NOT NULL THEN CONCAT(TRIM(per.nombre), ' ', COALESCE(TRIM(per.apellido_paterno), ''), ' ', COALESCE(TRIM(per.apellido_materno), '')) ELSE NULL END as docente"),
                    'p.nombre_periodo as periodo'
                )
                ->where('p.estatus', true)
                ->orderBy('g.clave_grupo')
                ->get();

            return response()->json($grupos);
        } catch (\Exception $e) {
            Log::error("Error en grupos(): " . $e->getMessage());
            return response()->json(['error' => 'Error al cargar grupos', 'detalle' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/docentes/disponibles
     */
    public function docentesDisponibles()
    {
        try {
            // Subquery: grupos del periodo activo por docente
            $cargaSubquery = DB::table('grupo as g')
                ->join('periodo as per', 'g.id_periodo', '=', 'per.id_periodo')
                ->where('per.estatus', true)
                ->select('g.id_docente', DB::raw('COUNT(*) as carga_periodo_actual'))
                ->groupBy('g.id_docente');

            $docentes = DB::table('docente as d')
                ->join('empleado as e', 'd.id_empleado', '=', 'e.id_empleado')
                ->join('persona as p', 'e.id_persona', '=', 'p.id_persona')
                ->leftJoinSub($cargaSubquery, 'carga', 'carga.id_docente', '=', 'd.id_docente')
                ->where('e.estatus', true)
                ->select(
                    'd.id_docente',
                    DB::raw("TRIM(CONCAT(p.nombre, ' ', COALESCE(p.apellido_paterno,''), ' ', COALESCE(p.apellido_materno,''))) as nombre"),
                    'e.numero_empleado',
                    'd.especialidad',
                    'd.grado_academico',
                    DB::raw('COALESCE(carga.carga_periodo_actual, 0) as carga_actual')
                )
                ->orderBy('p.apellido_paterno')
                ->get();

            return response()->json($docentes);
        } catch (\Exception $e) {
            Log::error("Error en docentesDisponibles(): " . $e->getMessage());
            return response()->json(['error' => 'Error al cargar docentes', 'detalle' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/asignacion-docente
     * Asignar o cambiar docente a un grupo
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_grupo'   => 'required|integer|exists:grupo,id_grupo',
            'id_docente' => 'required|integer|exists:docente,id_docente'
        ]);

        try {
            DB::table('grupo')
                ->where('id_grupo', $request->id_grupo)
                ->update(['id_docente' => $request->id_docente]);

            return response()->json(['message' => 'Asignación guardada correctamente']);
        } catch (\Exception $e) {
            Log::error("Error al asignar docente: " . $e->getMessage());
            return response()->json(['error' => 'No se pudo guardar la asignación'], 500);
        }
    }
}
