<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * DocumentoController
 *
 * Genera documentos oficiales en PDF usando dompdf.
 *
 * Instalación (una sola vez):
 *   composer require barryvdh/laravel-dompdf
 *
 * Rutas en api.php:
 *   Route::get('/documentos/constancia/{numero_control}',  [DocumentoController::class, 'constancia']);
 *   Route::get('/documentos/boleta/{numero_control}',      [DocumentoController::class, 'boleta']);
 *   Route::get('/documentos/certificado/{numero_control}', [DocumentoController::class, 'certificado']);
 *
 * Todos devuelven el PDF inline (para preview en browser) o como descarga.
 * Parámetro ?tipo=estudios|inscripcion|promedio en constancia.
 * Parámetro ?download=1 en cualquiera para forzar descarga.
 */
class DocumentoController extends Controller
{
    // ─── Datos base del alumno ─────────────────────────────────────────────

    private function datosAlumno(string $numero_control): ?object
    {
        return DB::table('alumno as a')
            ->join('persona as p',   'a.id_persona',  '=', 'p.id_persona')
            ->join('carrera as c',   'a.id_carrera',  '=', 'c.id_carrera')
            ->leftJoin('plan_estudio as pe', function ($j) {
                $j->on('pe.id_carrera', '=', 'a.id_carrera')
                  ->whereRaw('pe.id_plan = (SELECT MAX(id_plan) FROM plan_estudio WHERE id_carrera = a.id_carrera)');
            })
            ->leftJoin('departamento as d', 'c.id_departamento', '=', 'd.id_departamento')
            ->where('a.numero_control', $numero_control)
            ->select(
                'a.id_alumno',
                'a.numero_control',
                'a.semestre_actual',
                'a.fecha_ingreso',
                'a.estatus',
                DB::raw("CONCAT(p.nombre,' ',p.apellido_paterno,' ',COALESCE(p.apellido_materno,'')) as nombre_completo"),
                'p.curp',
                'c.nombre as carrera',
                'd.nombre as departamento',
                'pe.nombre_plan',
                'pe.total_creditos'
            )
            ->first();
    }

    private function periodoActivo(): ?object
    {
        return DB::table('periodo')
            ->where('estatus', 1)
            ->orderByDesc('id_periodo')
            ->first();
    }

    private function promedioAlumno(int $id_alumno): float
    {
        $promedio = DB::table('calificacion as c')
            ->join('evaluacion as e',  'c.id_evaluacion',  '=', 'e.id_evaluacion')
            ->join('inscripcion as i', 'c.id_inscripcion', '=', 'i.id_inscripcion')
            ->where('i.id_alumno', $id_alumno)
            ->avg(DB::raw('c.calificacion * e.porcentaje / 100'));

        return round((float) ($promedio ?? 0), 2);
    }

    // ─── Responder PDF ──────────────────────────────────────────────────────

    private function responderPDF($pdf, string $filename, Request $request)
    {
        if ($request->boolean('download')) {
            return $pdf->download($filename);
        }
        return $pdf->stream($filename);
    }

    // ─── 1. CONSTANCIA ─────────────────────────────────────────────────────

    /**
     * GET /api/documentos/constancia/{numero_control}?tipo=estudios|inscripcion|promedio
     */
    public function constancia(Request $request, string $numero_control)
    {
        $alumno = $this->datosAlumno($numero_control);

        if (!$alumno) {
            return response()->json(['error' => 'Alumno no encontrado.'], 404);
        }

        $tipo    = $request->query('tipo', 'estudios');
        $periodo = $this->periodoActivo();
        $promedio = $tipo === 'promedio' ? $this->promedioAlumno($alumno->id_alumno) : null;

        $titulos = [
            'estudios'    => 'CONSTANCIA DE ESTUDIOS',
            'inscripcion' => 'CONSTANCIA DE INSCRIPCIÓN',
            'promedio'    => 'CONSTANCIA DE PROMEDIO',
        ];

        $titulo = $titulos[$tipo] ?? 'CONSTANCIA DE ESTUDIOS';

        $pdf = Pdf::loadView('documentos.constancia', compact(
            'alumno', 'periodo', 'promedio', 'titulo', 'tipo'
        ))->setPaper('letter', 'portrait');

        return $this->responderPDF($pdf, "constancia_{$tipo}_{$numero_control}.pdf", $request);
    }

    // ─── 2. BOLETA ─────────────────────────────────────────────────────────

    /**
     * GET /api/documentos/boleta/{numero_control}?periodo_id=1
     * Si no se pasa periodo_id, usa el periodo activo.
     */
    public function boleta(Request $request, string $numero_control)
    {
        $alumno = $this->datosAlumno($numero_control);

        if (!$alumno) {
            return response()->json(['error' => 'Alumno no encontrado.'], 404);
        }

        $periodoId = $request->query('periodo_id');
        $periodo   = $periodoId
            ? DB::table('periodo')->where('id_periodo', $periodoId)->first()
            : $this->periodoActivo();

        if (!$periodo) {
            return response()->json(['error' => 'Periodo no encontrado.'], 404);
        }

        // Materias inscritas en el periodo con calificación ponderada
        $materias = DB::table('inscripcion as i')
            ->join('grupo as g',   'i.id_grupo',   '=', 'g.id_grupo')
            ->join('materia as m', 'g.id_materia', '=', 'm.id_materia')
            ->leftJoin(
                DB::raw('(
                    SELECT c.id_inscripcion,
                           SUM(c.calificacion * e.porcentaje / 100) as calificacion_final
                    FROM calificacion c
                    INNER JOIN evaluacion e ON c.id_evaluacion = e.id_evaluacion
                    GROUP BY c.id_inscripcion
                ) as cp'),
                'cp.id_inscripcion', '=', 'i.id_inscripcion'
            )
            ->where('i.id_alumno',  $alumno->id_alumno)
            ->where('g.id_periodo', $periodo->id_periodo)
            ->select(
                'm.clave',
                'm.nombre as materia',
                'm.creditos',
                DB::raw('ROUND(COALESCE(cp.calificacion_final, 0), 1) as calificacion'),
                DB::raw("CASE WHEN COALESCE(cp.calificacion_final, 0) >= 70 THEN 'A' ELSE 'R' END as resultado")
            )
            ->orderBy('m.nombre')
            ->get();

        $promedioPeriodo = $materias->avg('calificacion');
        $promedioPeriodo = $promedioPeriodo ? round($promedioPeriodo, 2) : 0;

        $pdf = Pdf::loadView('documentos.boleta', compact(
            'alumno', 'periodo', 'materias', 'promedioPeriodo'
        ))->setPaper('letter', 'portrait');

        return $this->responderPDF($pdf, "boleta_{$numero_control}_{$periodo->nombre_periodo}.pdf", $request);
    }

    // ─── 3. CERTIFICADO ────────────────────────────────────────────────────

    /**
     * GET /api/documentos/certificado/{numero_control}
     * Genera el certificado de estudios completo con todas las materias del plan.
     */
    public function certificado(Request $request, string $numero_control)
    {
        $alumno = $this->datosAlumno($numero_control);

        if (!$alumno) {
            return response()->json(['error' => 'Alumno no encontrado.'], 404);
        }

        // Todas las materias del plan con calificación
        $materias = DB::table('plan_materia as pm')
            ->join('plan_estudio as pe', 'pm.id_plan',    '=', 'pe.id_plan')
            ->join('materia as m',       'pm.id_materia', '=', 'm.id_materia')
            ->leftJoin(
                DB::raw('(
                    SELECT g.id_materia,
                           MAX(c.calificacion) as calificacion_final
                    FROM inscripcion i
                    JOIN grupo g       ON i.id_grupo       = g.id_grupo
                    JOIN calificacion c ON i.id_inscripcion = c.id_inscripcion
                    WHERE i.id_alumno = ' . (int) $alumno->id_alumno . '
                    GROUP BY g.id_materia
                ) as cal'),
                'cal.id_materia', '=', 'm.id_materia'
            )
            ->where('pe.id_carrera', DB::table('alumno')->where('id_alumno', $alumno->id_alumno)->value('id_carrera'))
            ->select(
                'pm.semestre',
                'm.clave',
                'm.nombre as materia',
                'm.creditos',
                'm.horas_teoria',
                'm.horas_practica',
                DB::raw('COALESCE(cal.calificacion_final, NULL) as calificacion'),
                DB::raw("CASE
                    WHEN cal.calificacion_final IS NULL THEN 'Pendiente'
                    WHEN cal.calificacion_final >= 70   THEN 'Acreditada'
                    ELSE 'Reprobada'
                END as estado")
            )
            ->orderBy('pm.semestre')
            ->orderBy('m.nombre')
            ->get();

        $creditosAcumulados = $materias
            ->where('estado', 'Acreditada')
            ->sum('creditos');

        $promedioGeneral = $materias
            ->whereNotNull('calificacion')
            ->avg('calificacion');
        $promedioGeneral = $promedioGeneral ? round($promedioGeneral, 2) : 0;

        $materiasPorSemestre = $materias->groupBy('semestre');

        $pdf = Pdf::loadView('documentos.certificado', compact(
            'alumno', 'materiasPorSemestre', 'creditosAcumulados', 'promedioGeneral'
        ))->setPaper('letter', 'portrait');

        return $this->responderPDF($pdf, "certificado_{$numero_control}.pdf", $request);
    }

    // ─── 4. ACTA ────────────────────────────────────────────────────────────

    /**
     * GET /api/documentos/acta/{grupo_id}
     * Genera el acta de calificaciones de un grupo en PDF.
     */
    public function acta(Request $request, int $grupo_id)
    {
        try {
            $grupo = DB::table('grupo as g')
                ->join('materia as m',       'g.id_materia',  '=', 'm.id_materia')
                ->leftJoin('docente as d',   'g.id_docente',  '=', 'd.id_docente')
                ->leftJoin('empleado as e',  'd.id_empleado', '=', 'e.id_empleado')
                ->leftJoin('persona as pd',  'e.id_persona',  '=', 'pd.id_persona')
                ->leftJoin('aula as a',      'g.id_aula',     '=', 'a.id_aula')
                ->leftJoin('periodo as per', 'g.id_periodo',  '=', 'per.id_periodo')
                ->where('g.id_grupo', $grupo_id)
                ->select(
                    'g.id_grupo', 'g.clave_grupo', 'g.capacidad',
                    'm.nombre as materia', 'm.clave as clave_materia', 'm.creditos',
                    DB::raw("COALESCE(CONCAT(pd.nombre,' ',pd.apellido_paterno,' ',COALESCE(pd.apellido_materno,'')), 'Sin asignar') as docente"),
                    'a.nombre as aula',
                    'per.nombre_periodo as periodo'
                )
                ->first();

            if (!$grupo) {
                return response()->json(['error' => 'Grupo no encontrado'], 404);
            }

            $alumnos = DB::table('inscripcion as i')
                ->join('alumno as a',  'i.id_alumno',  '=', 'a.id_alumno')
                ->join('persona as p', 'a.id_persona', '=', 'p.id_persona')
                ->where('i.id_grupo', $grupo_id)
                ->whereIn('i.estatus', ['Activo', 'activo', 'inscrito'])
                ->select(
                    'a.numero_control',
                    DB::raw("CONCAT(p.apellido_paterno,' ',p.apellido_materno,' ',p.nombre) as nombre"),
                    'i.id_inscripcion'
                )
                ->orderBy('p.apellido_paterno')
                ->get()
                ->map(function ($alumno) {
                    $califs = DB::table('calificacion as c')
                        ->join('evaluacion as ev', 'c.id_evaluacion', '=', 'ev.id_evaluacion')
                        ->where('c.id_inscripcion', $alumno->id_inscripcion)
                        ->pluck('c.calificacion', 'ev.nombre');

                    $getUnidad = fn($n) => $califs->first(fn($v, $k) =>
                        str_contains(strtolower($k), 'unidad '.$n) ||
                        str_contains(strtolower($k), 'u'.$n) ||
                        str_contains(strtolower($k), 'parcial '.$n)
                    );

                    $promedio = DB::table('calificacion as c')
                        ->join('evaluacion as ev', 'c.id_evaluacion', '=', 'ev.id_evaluacion')
                        ->where('c.id_inscripcion', $alumno->id_inscripcion)
                        ->sum(DB::raw('c.calificacion * ev.porcentaje / 100'));

                    return [
                        'numero_control' => $alumno->numero_control,
                        'nombre'         => trim($alumno->nombre),
                        'u1'             => $getUnidad(1),
                        'u2'             => $getUnidad(2),
                        'u3'             => $getUnidad(3),
                        'u4'             => $getUnidad(4),
                        'u5'             => $getUnidad(5),
                        'final'          => $promedio ? round((float)$promedio, 1) : null,
                        'resultado'      => $promedio >= 70 ? 'A' : 'R',
                    ];
                });

            $aprobados  = $alumnos->where('resultado', 'A')->count();
            $reprobados = $alumnos->where('resultado', 'R')->count();

            $pdf = Pdf::loadView('documentos.acta', compact(
                'grupo', 'alumnos', 'aprobados', 'reprobados'
            ))->setPaper('letter', 'landscape');

            $filename = "acta_{$grupo->clave_grupo}_{$grupo_id}.pdf";
            return $request->boolean('download') ? $pdf->download($filename) : $pdf->stream($filename);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ─── 5. CARGA ACADÉMICA ─────────────────────────────────────────────────

    /**
     * GET /api/documentos/carga/{numero_control}?periodo=Ene-Jun 2026
     * Genera la carga académica (materias inscritas) del alumno en un periodo.
     */
    public function carga(Request $request, string $numero_control)
    {
        $alumno = $this->datosAlumno($numero_control);

        if (!$alumno) {
            return response()->json(['error' => 'Alumno no encontrado'], 404);
        }

        $periodoNombre = $request->query('periodo');
        $periodo = $periodoNombre
            ? DB::table('periodo')->where('nombre_periodo', $periodoNombre)->first()
            : $this->periodoActivo();

        if (!$periodo) {
            return response()->json(['error' => 'Periodo no encontrado'], 404);
        }

        $materias = DB::table('inscripcion as i')
            ->join('grupo as g',         'i.id_grupo',   '=', 'g.id_grupo')
            ->join('materia as m',        'g.id_materia', '=', 'm.id_materia')
            ->leftJoin('docente as d',    'g.id_docente', '=', 'd.id_docente')
            ->leftJoin('empleado as em',  'd.id_empleado','=', 'em.id_empleado')
            ->leftJoin('persona as pd',   'em.id_persona','=', 'pd.id_persona')
            ->leftJoin('aula as a',       'g.id_aula',    '=', 'a.id_aula')
            ->where('i.id_alumno',  $alumno->id_alumno)
            ->where('g.id_periodo', $periodo->id_periodo)
            ->whereIn('i.estatus', ['Activo', 'activo', 'inscrito'])
            ->select(
                'm.clave', 'm.nombre as materia', 'm.creditos',
                'g.clave_grupo',
                DB::raw("COALESCE(CONCAT(pd.nombre,' ',pd.apellido_paterno), 'Sin asignar') as docente"),
                'a.nombre as aula',
                'g.dia', 'g.hora_inicio', 'g.hora_fin'
            )
            ->get();

        $totalCreditos = $materias->sum('creditos');

        $pdf = Pdf::loadView('documentos.carga', compact(
            'alumno', 'periodo', 'materias', 'totalCreditos'
        ))->setPaper('letter', 'portrait');

        $filename = "carga_{$numero_control}_{$periodo->nombre_periodo}.pdf";
        return $request->boolean('download') ? $pdf->download($filename) : $pdf->stream($filename);
    }

    // ─── 6. ACTA DE RESIDENCIA ──────────────────────────────────────────────

    /**
     * GET /api/documentos/residencia/{id_solicitud}
     * Genera el acta de residencia profesional de un alumno.
     */
    public function actaResidencia(Request $request, int $id_solicitud)
    {
        try {
            $solicitud = DB::table('solicitud_residencia as sr')
                ->join('alumno as a',   'sr.id_alumno',  '=', 'a.id_alumno')
                ->join('persona as p',  'a.id_persona',  '=', 'p.id_persona')
                ->join('carrera as c',  'a.id_carrera',  '=', 'c.id_carrera')
                ->join('periodo as per','sr.id_periodo', '=', 'per.id_periodo')
                ->where('sr.id_solicitud', $id_solicitud)
                ->select(
                    'sr.*',
                    DB::raw("CONCAT(p.nombre,' ',p.apellido_paterno,' ',COALESCE(p.apellido_materno,'')) as nombre_alumno"),
                    'a.numero_control', 'p.curp',
                    'c.nombre as carrera',
                    'per.nombre_periodo as periodo'
                )
                ->first();

            if (!$solicitud) {
                return response()->json(['error' => 'Solicitud no encontrada'], 404);
            }

            $proyecto = DB::table('proyecto_residencia as pr')
                ->join('empresa_residencia as er',        'pr.id_empresa',        '=', 'er.id_empresa')
                ->leftJoin('asesor_externo as ae',         'pr.id_asesor_externo', '=', 'ae.id_asesor_externo')
                ->leftJoin('docente as d',                 'pr.id_docente_asesor', '=', 'd.id_docente')
                ->leftJoin('empleado as em',               'd.id_empleado',        '=', 'em.id_empleado')
                ->leftJoin('persona as pd',                'em.id_persona',        '=', 'pd.id_persona')
                ->leftJoin('evaluacion_residencia as ev',  'ev.id_proyecto',       '=', 'pr.id_proyecto')
                ->where('pr.id_solicitud', $id_solicitud)
                ->select(
                    'pr.*',
                    'er.nombre as empresa', 'er.ciudad', 'er.giro',
                    'ae.nombre as asesor_externo', 'ae.puesto as puesto_asesor',
                    DB::raw("COALESCE(CONCAT(pd.nombre,' ',pd.apellido_paterno), 'Sin asignar') as asesor_interno"),
                    'ev.calificacion_final', 'ev.carta_liberacion'
                )
                ->first();

            $folio = strtoupper(substr(md5($id_solicitud . date('Ymd')), 0, 10));

            $pdf = Pdf::loadView('documentos.acta_residencia', compact(
                'solicitud', 'proyecto', 'folio'
            ))->setPaper('letter', 'portrait');

            $filename = "acta_residencia_{$solicitud->numero_control}.pdf";
            return $request->boolean('download') ? $pdf->download($filename) : $pdf->stream($filename);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}