<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alumno;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CertificadoController extends Controller
{
    public function generarCertificado($numero_control, Request $request)
    {
        // 1. Obtener alumno
        $alumno = Alumno::with(['persona', 'carrera'])
            ->where('numero_control', $numero_control)
            ->firstOrFail();

        // 2. Obtener total de materias cursadas
        $totalMaterias = DB::select("
            SELECT COUNT(DISTINCT m.id_materia) as total
            FROM inscripcion i
            INNER JOIN grupo g ON i.id_grupo = g.id_grupo
            INNER JOIN materia m ON g.id_materia = m.id_materia
            WHERE i.id_alumno = ?
        ", [$alumno->id_alumno]);

        $totalMaterias = $totalMaterias[0]->total ?? 0;

        // 3. Obtener materias aprobadas (calificación >= 6)
        $aprobadas = DB::select("
            SELECT COUNT(DISTINCT m.id_materia) as total
            FROM inscripcion i
            INNER JOIN grupo g ON i.id_grupo = g.id_grupo
            INNER JOIN materia m ON g.id_materia = m.id_materia
            INNER JOIN calificacion c ON i.id_inscripcion = c.id_inscripcion
            WHERE i.id_alumno = ?
            AND c.calificacion >= 6
        ", [$alumno->id_alumno]);

        $aprobadas = $aprobadas[0]->total ?? 0;

        // 4. Obtener promedio general
        $promedio = DB::select("
            SELECT AVG(c.calificacion) as promedio
            FROM inscripcion i
            INNER JOIN calificacion c ON i.id_inscripcion = c.id_inscripcion
            WHERE i.id_alumno = ?
        ", [$alumno->id_alumno]);

        $promedioGeneral = round($promedio[0]->promedio ?? 0, 2);

        // 5. Obtener nombre completo
        $nombreCompleto = 'N/A';
        if ($alumno->persona) {
            $nombreCompleto = trim(
                ($alumno->persona->nombre ?? '') . ' ' .
                ($alumno->persona->apellido_paterno ?? '') . ' ' .
                ($alumno->persona->apellido_materno ?? '')
            );
        }

        // 6. Datos para la vista
        $data = [
            'tipo_documento' => 'CERTIFICADO DE ESTUDIOS',
            'numero_certificado' => 'CERT-' . date('Y') . '-' . $alumno->numero_control,
            'fecha_emision' => now(),
            'folio' => 'CERT-' . $alumno->numero_control . '-' . date('YmdHis'),
            'numero_control' => $alumno->numero_control,
            'nombre_completo' => $nombreCompleto,
            'carrera' => $alumno->carrera ? $alumno->carrera->nombre : 'N/A',
            'semestre' => $alumno->semestre_actual ?? 'N/A',
            'fecha_ingreso' => $alumno->fecha_ingreso ? date('d/m/Y', strtotime($alumno->fecha_ingreso)) : 'N/A',
            'total_materias' => $totalMaterias,
            'materias_aprobadas' => $aprobadas,
            'materias_reprobadas' => $totalMaterias - $aprobadas,
            'promedio_general' => $promedioGeneral
        ];

        // 7. Generar PDF
        $pdf = Pdf::loadView('pdf.certificado', $data);
        $pdf->setPaper('letter', 'portrait');

        $filename = sprintf('CERTIFICADO_%s_%s.pdf', $alumno->numero_control, date('Ymd'));

        return $pdf->download($filename);
    }
}
