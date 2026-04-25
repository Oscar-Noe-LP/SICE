<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class HistorialInscripcionController extends Controller
{
    /**
     * 🔎 Buscar alumno por número de control
     */
    public function buscarPorControl($numero_control)
    {
        if (!$numero_control) {
            return response()->json([
                'error' => 'Debes escribir un número de control'
            ], 422);
        }

        $alumno = DB::table('alumno as a')
            ->join('persona as p', 'a.id_persona', '=', 'p.id_persona')
            ->join('carrera as c', 'a.id_carrera', '=', 'c.id_carrera')
            ->select(
                'a.id_alumno',
                'a.numero_control',
                DB::raw("CONCAT(p.nombre, ' ', p.apellido_paterno, ' ', p.apellido_materno) as nombre_alumno"),
                'c.nombre as nombre_carrera'
            )
            ->where('a.numero_control', $numero_control)
            ->first();

        if (!$alumno) {
            return response()->json([
                'error' => 'Alumno no encontrado'
            ], 404);
        }

        return response()->json($alumno);
    }

    /**
     * 📚 Historial agrupado por periodo
     */
    public function historial($id_alumno)
    {
        $inscripciones = DB::table('inscripcion as i')
            ->join('grupo as g', 'i.id_grupo', '=', 'g.id_grupo')
            ->join('materia as m', 'g.id_materia', '=', 'm.id_materia')
            ->join('periodo as p', 'g.id_periodo', '=', 'p.id_periodo')
            ->leftJoin('aula as au', 'g.id_aula', '=', 'au.id_aula')

            // docente -> empleado -> persona
            ->leftJoin('docente as d', 'g.id_docente', '=', 'd.id_docente')
            ->leftJoin('empleado as e', 'd.id_empleado', '=', 'e.id_empleado')
            ->leftJoin('persona as pd', 'e.id_persona', '=', 'pd.id_persona')

            // 🔥 cálculo de calificación final
            ->leftJoin('calificacion as cal', 'i.id_inscripcion', '=', 'cal.id_inscripcion')

            ->select(
                'i.id_inscripcion',
                'p.id_periodo',
                'p.nombre_periodo',
                'g.clave_grupo',
                'm.nombre as nombre_materia',

                DB::raw("CONCAT(pd.nombre, ' ', pd.apellido_paterno) as nombre_docente"),

                'au.nombre as nombre_aula',
                'i.fecha_inscripcion',
                'i.estatus',

                // 🔥 PROMEDIO de calificaciones
                DB::raw("ROUND(AVG(cal.calificacion), 0) as calificacion_final")
            )
            ->where('i.id_alumno', $id_alumno)
            ->groupBy(
                'i.id_inscripcion',
                'p.id_periodo',
                'p.nombre_periodo',
                'g.clave_grupo',
                'm.nombre',
                'pd.nombre',
                'pd.apellido_paterno',
                'au.nombre',
                'i.fecha_inscripcion',
                'i.estatus'
            )
            ->orderBy('p.id_periodo', 'desc')
            ->get();

        // 🔥 Agrupar por periodo (igual que tu frontend espera)
        $agrupado = $inscripciones->groupBy('id_periodo')->map(function ($items) {
            return [
                'id_periodo' => $items[0]->id_periodo,
                'nombre_periodo' => $items[0]->nombre_periodo,
                'inscripciones' => $items->values()
            ];
        })->values();

        return response()->json($agrupado);
    }

    /**
     * 📄 Exportar PDF
     */
    public function exportar($id_alumno)
    {
        $historial = json_decode($this->historial($id_alumno)->getContent());

        $alumno = DB::table('alumno as a')
            ->join('persona as p', 'a.id_persona', '=', 'p.id_persona')
            ->select(
                'a.numero_control',
                DB::raw("CONCAT(p.nombre, ' ', p.apellido_paterno, ' ', p.apellido_materno) as nombre")
            )
            ->where('a.id_alumno', $id_alumno)
            ->first();

        $pdf = Pdf::loadView('pdf.historial', [
            'periodos' => $historial,
            'alumno' => $alumno
        ]);

        return $pdf->download("historial_{$alumno->numero_control}.pdf");
    }
}