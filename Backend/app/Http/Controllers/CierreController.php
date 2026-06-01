<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CierreController extends Controller
{
    public function kpis()
    {
        $periodo = DB::table('periodo')
            ->where('activo', 1)
            ->first();

        if (!$periodo) {
            return response()->json([
                'success' => false,
                'message' => 'No hay periodo activo'
            ]);
        }

        $alumnos = DB::table('alumno')
            ->where('estatus', 'Activo')
            ->count();
            
        $grupos = DB::table('grupo')
            ->where('id_periodo', $periodo->id_periodo)
            ->count();

        $gruposAbiertos = DB::table('grupo')
            ->where('id_periodo', $periodo->id_periodo)
            ->where('estatus', 1)
            ->count();

        $gruposCerrados = $grupos - $gruposAbiertos;

        $promedio = DB::table('detalle_kardex')
            ->avg('calificacion_final');

        return response()->json([
            'success' => true,
            'periodo' => $periodo->nombre_periodo,
            'kpis' => [
                'alumnos' => $alumnos,
                'grupos' => $grupos,
                'grupos_activos' => $gruposAbiertos,
                'grupos_cerrados' => $gruposCerrados,
                'promedio_general' => round($promedio, 2),

                // placeholders por ahora (ya reales abajo en validaciones)
                'actas_pendientes' => 0,
                'calificaciones_pendientes' => 0
            ]
        ]);
    }

    /**
     * CALIFICACIONES PENDIENTES:
     * evaluaciones sin calificación por alumno inscrito
     */
    public function calificacionesPendientes()
    {
        $data = DB::select("
            SELECT 
                i.id_grupo,
                g.clave_grupo,
                COUNT(DISTINCT i.id_alumno) AS alumnos_sin_calificar
            FROM inscripcion i
            JOIN grupo g ON g.id_grupo = i.id_grupo
            JOIN evaluacion e ON e.id_grupo = g.id_grupo
            LEFT JOIN calificacion c 
                ON c.id_inscripcion = i.id_inscripcion 
                AND c.id_evaluacion = e.id_evaluacion
            WHERE c.id_calificacion IS NULL
            GROUP BY i.id_grupo, g.clave_grupo
        ");

        return response()->json([
            'grupos' => $data
        ]);
    }

    /**
     * ACTAS PENDIENTES:
     * grupos con evaluaciones incompletas
     */
    public function actasPendientes()
    {
        $data = DB::select("
            SELECT 
                g.id_grupo,
                g.clave_grupo,
                COUNT(e.id_evaluacion) AS evaluaciones_totales,
                COUNT(c.id_calificacion) AS evaluaciones_capturadas
            FROM grupo g
            JOIN evaluacion e ON e.id_grupo = g.id_grupo
            LEFT JOIN calificacion c 
                ON c.id_evaluacion = e.id_evaluacion
            WHERE g.estatus = 1
            GROUP BY g.id_grupo, g.clave_grupo
            HAVING evaluaciones_capturadas < evaluaciones_totales
        ");

        return response()->json([
            'actas' => $data
        ]);
    }

    /**
     * GRUPOS ABIERTOS / CERRADOS
     */
    public function gruposAbiertos()
    {
        $grupos = DB::table('grupo')
            ->where('estatus', 1)
            ->get();

        return response()->json([
            'grupos' => $grupos
        ]);
    }

    public function gruposCerrados()
    {
        $grupos = DB::table('grupo')
            ->where('estatus', 0)
            ->get();

        return response()->json([
            'grupos' => $grupos
        ]);
    }
}