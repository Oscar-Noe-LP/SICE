<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alumno;
use App\Models\Periodo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ConstanciaController extends Controller
{
    public function generarConstancia($numero_control, Request $request)
    {
        $alumno = Alumno::with(['persona', 'carrera'])
            ->where('numero_control', $numero_control)
            ->firstOrFail();

        $tipo = $request->get('tipo', 'estudios');
        $periodoId = $request->get('periodo');
        $periodo = $periodoId ? Periodo::find($periodoId) : null;

        $tiposConstancia = [
            'estudios' => 'CONSTANCIA DE ESTUDIOS',
            'inscripcion' => 'CONSTANCIA DE INSCRIPCIÓN',
            'promedio' => 'CONSTANCIA DE PROMEDIO',
            'beca' => 'CONSTANCIA DE BECA',
            'visa' => 'CONSTANCIA PARA TRÁMITE DE VISA',
            'trabajo' => 'CONSTANCIA PARA TRÁMITE LABORAL',
            'imss' => 'CONSTANCIA PARA IMSS'
        ];

        $nombreCompleto = 'N/A';
        if ($alumno->persona) {
            $nombreCompleto = trim(
                ($alumno->persona->nombre ?? '') . ' ' .
                ($alumno->persona->apellido_paterno ?? '') . ' ' .
                ($alumno->persona->apellido_materno ?? '')
            );
        }

        $data = [
            'tipo_documento' => 'CONSTANCIA',
            'tipo_texto' => $tiposConstancia[$tipo] ?? 'CONSTANCIA ESCOLAR',
            'subtitulo' => $tiposConstancia[$tipo] ?? 'CONSTANCIA ESCOLAR',
            'periodo' => $periodo,
            'fecha_emision' => now(),
            'folio' => 'CONST-' . $alumno->numero_control . '-' . date('YmdHis'),
            'numero_control' => $alumno->numero_control,
            'nombre_completo' => $nombreCompleto,
            'carrera' => $alumno->carrera ? $alumno->carrera->nombre : 'N/A',
            'semestre' => $alumno->semestre_actual ?? 'N/A',
            'promedio' => null
        ];

        $pdf = Pdf::loadView('pdf.constancia', $data);
        $pdf->setPaper('letter', 'portrait');

        $filename = sprintf('CONSTANCIA_%s_%s_%s.pdf', strtoupper($tipo), $alumno->numero_control, $periodoId ?? date('Y'));

        return $pdf->download($filename);
    }
}
