<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alumno;
use App\Models\Periodo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BoletaController extends Controller
{
    public function generarBoleta($numero_control, Request $request)
    {
        // 1. Obtener alumno
        $alumno = Alumno::with(['persona', 'carrera'])
            ->where('numero_control', $numero_control)
            ->firstOrFail();

        $periodoId = $request->get('periodo');
        $periodo = $periodoId ? Periodo::find($periodoId) : null;

        // 2. Consulta SQL corregida (agrupar por todos los campos no agregados)
        $query = "
            SELECT
                m.clave,
                m.nombre as materia_nombre,
                m.creditos,
                COALESCE(AVG(c.calificacion), 0) as calificacion
            FROM inscripcion i
            INNER JOIN grupo g ON i.id_grupo = g.id_grupo
            INNER JOIN materia m ON g.id_materia = m.id_materia
            LEFT JOIN calificacion c ON i.id_inscripcion = c.id_inscripcion
            WHERE i.id_alumno = ?
        ";

        $bindings = [$alumno->id_alumno];

        if ($periodoId) {
            $query .= " AND g.id_periodo = ?";
            $bindings[] = $periodoId;
        }

        // ⭐ Agrupar por todos los campos que seleccionamos (excepto el AVG)
        $query .= " GROUP BY m.id_materia, m.clave, m.nombre, m.creditos";

        $materias = DB::select($query, $bindings);

        // Procesar materias
        $materiasList = [];
        foreach ($materias as $materia) {
            $calificacion = round($materia->calificacion, 2);
            $materiasList[] = (object)[
                'clave' => $materia->clave ?? 'N/A',
                'nombre' => $materia->materia_nombre ?? 'N/A',
                'creditos' => $materia->creditos ?? 0,
                'calificacion' => $calificacion,
                'estado' => $calificacion >= 6 ? 'APROBADA' : ($calificacion > 0 ? 'REPROBADA' : 'SIN CALIFICACIÓN')
            ];
        }

        // Obtener nombre completo
        $nombreCompleto = 'N/A';
        if ($alumno->persona) {
            $nombreCompleto = trim(
                ($alumno->persona->nombre ?? '') . ' ' .
                ($alumno->persona->apellido_paterno ?? '') . ' ' .
                ($alumno->persona->apellido_materno ?? '')
            );
        }

        $promedioPeriodo = count($materiasList) > 0
            ? collect($materiasList)->avg('calificacion')
            : 0;

        $data = [
            'materias' => $materiasList,
            'periodo' => $periodo,
            'tipo_documento' => 'BOLETA DE CALIFICACIONES',
            'subtitulo' => 'DOCUMENTO OFICIAL DE CALIFICACIONES',
            'fecha_emision' => now(),
            'folio' => 'BOL-' . $alumno->numero_control . '-' . date('YmdHis'),
            'numero_control' => $alumno->numero_control,
            'nombre_completo' => $nombreCompleto,
            'carrera' => $alumno->carrera ? $alumno->carrera->nombre : 'N/A',
            'semestre' => $alumno->semestre_actual ?? 'N/A',
            'promedio_periodo' => round($promedioPeriodo, 2),
            'materias_cursadas' => count($materiasList),
            'materias_aprobadas' => collect($materiasList)->where('estado', 'APROBADA')->count(),
            'materias_reprobadas' => collect($materiasList)->where('estado', 'REPROBADA')->count()
        ];

        $pdf = Pdf::loadView('pdf.boleta', $data);
        $pdf->setPaper('letter', 'portrait');

        $filename = sprintf('BOLETA_%s_%s.pdf', $alumno->numero_control, $periodoId ?? date('Y'));

        return $pdf->download($filename);
    }
}
