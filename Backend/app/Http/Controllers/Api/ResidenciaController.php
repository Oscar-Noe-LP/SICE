<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * ResidenciaController
 *
 * Gestión completa del módulo de Residencias Profesionales.
 *
 * Rutas en api.php:
 *   Route::get   ('/residencias',                     [ResidenciaController::class, 'index']);
 *   Route::get   ('/residencias/{id}',                [ResidenciaController::class, 'show']);
 *   Route::post  ('/residencias',                     [ResidenciaController::class, 'store']);
 *   Route::put   ('/residencias/{id}',                [ResidenciaController::class, 'update']);
 *   Route::get   ('/residencias/elegibles',           [ResidenciaController::class, 'elegibles']);
 *   Route::get   ('/residencias/{id}/reportes',       [ResidenciaController::class, 'reportes']);
 *   Route::post  ('/residencias/{id}/reportes',       [ResidenciaController::class, 'guardarReporte']);
 *   Route::post  ('/residencias/{id}/evaluacion',     [ResidenciaController::class, 'guardarEvaluacion']);
 *   Route::get   ('/empresas-residencia',             [ResidenciaController::class, 'empresas']);
 */
class ResidenciaController extends Controller
{
    // ─── Umbral de créditos para elegibilidad ─────────────────────────────────
    private const PORCENTAJE_MINIMO = 0.80;

    // =========================================================================
    // GET /api/residencias
    // =========================================================================

    /**
     * Lista todas las solicitudes de residencia con datos del alumno,
     * empresa y proyecto (si existe).
     *
     * Filtros opcionales: ?estatus=En curso&carrera_id=1&periodo_id=1
     */
    public function index(Request $request)
    {
        try {
            $query = DB::table('solicitud_residencia as sr')
                ->join('alumno as a',       'sr.id_alumno',  '=', 'a.id_alumno')
                ->join('persona as p',       'a.id_persona',  '=', 'p.id_persona')
                ->join('carrera as c',       'a.id_carrera',  '=', 'c.id_carrera')
                ->join('periodo as per',     'sr.id_periodo', '=', 'per.id_periodo')
                ->leftJoin('proyecto_residencia as pr', 'pr.id_solicitud', '=', 'sr.id_solicitud')
                ->leftJoin('empresa_residencia as er',  'pr.id_empresa',   '=', 'er.id_empresa')
                ->leftJoin('docente as d',              'pr.id_docente_asesor', '=', 'd.id_docente')
                ->leftJoin('empleado as em',            'd.id_empleado',   '=', 'em.id_empleado')
                ->leftJoin('persona as pd',             'em.id_persona',   '=', 'pd.id_persona')
                ->select(
                    'sr.id_solicitud',
                    'sr.estatus',
                    'sr.fecha_solicitud',
                    'sr.creditos_acumulados',
                    'sr.creditos_requeridos',
                    'sr.observaciones',
                    'a.id_alumno',
                    'a.numero_control',
                    DB::raw("CONCAT(p.nombre,' ',p.apellido_paterno,' ',COALESCE(p.apellido_materno,'')) as nombre_alumno"),
                    'c.nombre as carrera',
                    'a.semestre_actual',
                    'per.nombre_periodo as periodo',
                    'pr.id_proyecto',
                    'pr.nombre_proyecto',
                    'pr.horas_requeridas',
                    'pr.horas_acumuladas',
                    'pr.fecha_inicio',
                    'pr.fecha_fin_estimada',
                    'er.id_empresa',
                    'er.nombre as empresa',
                    DB::raw("COALESCE(CONCAT(pd.nombre,' ',pd.apellido_paterno,' ',COALESCE(pd.apellido_materno,'')), 'Sin asignar') as asesor_interno")
                );

            // Filtros opcionales
            if ($request->filled('estatus')) {
                $query->where('sr.estatus', $request->estatus);
            }
            if ($request->filled('carrera_id')) {
                $query->where('a.id_carrera', $request->carrera_id);
            }
            if ($request->filled('periodo_id')) {
                $query->where('sr.id_periodo', $request->periodo_id);
            }

            $residencias = $query
                ->orderByDesc('sr.fecha_solicitud')
                ->get()
                ->map(fn($r) => $this->formatearResidencia($r));

            return response()->json([
                'total'       => $residencias->count(),
                'residencias' => $residencias,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // GET /api/residencias/{id}
    // =========================================================================

    /**
     * Detalle completo de una solicitud: alumno, proyecto, empresa,
     * asesor externo, reportes y evaluación final.
     */
    public function show(int $id)
    {
        try {
            $solicitud = DB::table('solicitud_residencia as sr')
                ->join('alumno as a',   'sr.id_alumno',  '=', 'a.id_alumno')
                ->join('persona as p',  'a.id_persona',  '=', 'p.id_persona')
                ->join('carrera as c',  'a.id_carrera',  '=', 'c.id_carrera')
                ->join('periodo as per','sr.id_periodo', '=', 'per.id_periodo')
                ->leftJoin('kardex as k', 'k.id_alumno', '=', 'a.id_alumno')
                ->where('sr.id_solicitud', $id)
                ->select(
                    'sr.*',
                    DB::raw("CONCAT(p.nombre,' ',p.apellido_paterno,' ',COALESCE(p.apellido_materno,'')) as nombre_alumno"),
                    'a.numero_control',
                    'a.semestre_actual',
                    'a.estatus as estatus_alumno',
                    'c.nombre as carrera',
                    'per.nombre_periodo as periodo',
                    'k.promedio_general'
                )
                ->first();

            if (!$solicitud) {
                return response()->json(['error' => 'Solicitud no encontrada'], 404);
            }

            // Proyecto y empresa
            $proyecto = DB::table('proyecto_residencia as pr')
                ->join('empresa_residencia as er',        'pr.id_empresa',        '=', 'er.id_empresa')
                ->leftJoin('asesor_externo as ae',         'pr.id_asesor_externo', '=', 'ae.id_asesor_externo')
                ->leftJoin('docente as d',                 'pr.id_docente_asesor', '=', 'd.id_docente')
                ->leftJoin('empleado as em',               'd.id_empleado',        '=', 'em.id_empleado')
                ->leftJoin('persona as pd',                'em.id_persona',        '=', 'pd.id_persona')
                ->where('pr.id_solicitud', $id)
                ->select(
                    'pr.*',
                    'er.nombre as empresa',
                    'er.giro',
                    'er.ciudad',
                    'er.correo_contacto',
                    'ae.nombre as asesor_externo',
                    'ae.puesto as puesto_asesor_externo',
                    'ae.correo as correo_asesor_externo',
                    DB::raw("COALESCE(CONCAT(pd.nombre,' ',pd.apellido_paterno,' ',COALESCE(pd.apellido_materno,'')), 'Sin asignar') as asesor_interno")
                )
                ->first();

            // Reportes
            $reportes = $proyecto
                ? DB::table('reporte_residencia')
                    ->where('id_proyecto', $proyecto->id_proyecto)
                    ->orderBy('numero_reporte')
                    ->get()
                : collect();

            // Evaluación final
            $evaluacion = $proyecto
                ? DB::table('evaluacion_residencia')
                    ->where('id_proyecto', $proyecto->id_proyecto)
                    ->first()
                : null;

            return response()->json([
                'solicitud'  => $solicitud,
                'proyecto'   => $proyecto,
                'reportes'   => $reportes,
                'evaluacion' => $evaluacion,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // POST /api/residencias
    // =========================================================================

    /**
     * Registra una nueva solicitud de residencia.
     * Valida automáticamente que el alumno tenga el 80% de créditos requeridos.
     *
     * Body: { id_alumno, id_periodo? }
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_alumno'  => 'required|integer|exists:alumno,id_alumno',
            'id_periodo' => 'nullable|integer|exists:periodo,id_periodo',
        ]);

        try {
            $alumno = DB::table('alumno as a')
                ->join('persona as p', 'a.id_persona', '=', 'p.id_persona')
                ->join('carrera as c', 'a.id_carrera', '=', 'c.id_carrera')
                ->where('a.id_alumno', $request->id_alumno)
                ->select('a.*', 'c.nombre as carrera',
                    DB::raw("CONCAT(p.nombre,' ',p.apellido_paterno) as nombre"))
                ->first();

            if (!$alumno) {
                return response()->json(['error' => 'Alumno no encontrado'], 404);
            }

            if ($alumno->estatus !== 'Activo') {
                return response()->json([
                    'error' => 'Solo alumnos con estatus Activo pueden solicitar residencia.'
                ], 422);
            }

            // Verificar solicitud activa previa
            $solicitudActiva = DB::table('solicitud_residencia')
                ->where('id_alumno', $request->id_alumno)
                ->whereIn('estatus', ['Pendiente', 'Aprobada', 'En curso'])
                ->first();

            if ($solicitudActiva) {
                return response()->json([
                    'error' => 'El alumno ya tiene una solicitud activa (estatus: ' . $solicitudActiva->estatus . ').'
                ], 422);
            }

            // Créditos acumulados del kardex
            $kardex = DB::table('kardex')
                ->where('id_alumno', $request->id_alumno)
                ->first();

            $creditosAcumulados = $kardex->creditos_acumulados ?? 0;

            // Créditos requeridos: 80% del plan vigente
            $planEstudio = DB::table('plan_estudio')
                ->where('id_carrera', $alumno->id_carrera)
                ->where('estatus', 1)
                ->orderByDesc('id_plan')
                ->first();

            if (!$planEstudio) {
                return response()->json([
                    'error' => 'No se encontró plan de estudios activo para la carrera del alumno.'
                ], 422);
            }

            $creditosRequeridos = (int) round($planEstudio->total_creditos * self::PORCENTAJE_MINIMO);

            if ($creditosAcumulados < $creditosRequeridos) {
                return response()->json([
                    'error'               => 'El alumno no cumple el mínimo de créditos requeridos para residencia.',
                    'creditos_acumulados' => $creditosAcumulados,
                    'creditos_requeridos' => $creditosRequeridos,
                    'porcentaje_actual'   => round(($creditosAcumulados / $planEstudio->total_creditos) * 100, 1),
                ], 422);
            }

            // Periodo activo si no se especifica
            $idPeriodo = $request->id_periodo
                ?? DB::table('periodo')->where('estatus', 1)->orderByDesc('id_periodo')->value('id_periodo');

            if (!$idPeriodo) {
                return response()->json(['error' => 'No hay periodo activo configurado.'], 422);
            }

            $idSolicitud = DB::table('solicitud_residencia')->insertGetId([
                'id_alumno'           => $request->id_alumno,
                'id_periodo'          => $idPeriodo,
                'creditos_acumulados' => $creditosAcumulados,
                'creditos_requeridos' => $creditosRequeridos,
                'fecha_solicitud'     => now(),
                'estatus'             => 'Pendiente',
                'observaciones'       => $request->observaciones,
            ]);

            BitacoraService::registrar('INSERT', 'solicitud_residencia', $idSolicitud, [], [
                'id_alumno' => $request->id_alumno,
                'estatus'   => 'Pendiente',
            ]);

            return response()->json([
                'success'     => true,
                'message'     => 'Solicitud de residencia registrada correctamente.',
                'id_solicitud'=> $idSolicitud,
                'alumno'      => $alumno->nombre,
                'creditos_acumulados' => $creditosAcumulados,
                'creditos_requeridos' => $creditosRequeridos,
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // PUT /api/residencias/{id}
    // =========================================================================

    /**
     * Actualiza el estatus de una solicitud y/o asigna proyecto/empresa.
     *
     * Body: {
     *   estatus?,           // Pendiente|Aprobada|Rechazada|En curso|Concluida|No acreditada
     *   observaciones?,
     *   id_empresa?,
     *   id_asesor_externo?,
     *   id_docente_asesor?,
     *   nombre_proyecto?,
     *   descripcion?,
     *   fecha_inicio?,
     *   fecha_fin_estimada?
     * }
     */
    public function update(Request $request, int $id)
    {
        $request->validate([
            'estatus' => 'sometimes|in:Pendiente,Aprobada,Rechazada,En curso,Concluida,No acreditada',
        ]);

        try {
            $solicitud = DB::table('solicitud_residencia')->where('id_solicitud', $id)->first();

            if (!$solicitud) {
                return response()->json(['error' => 'Solicitud no encontrada'], 404);
            }

            $anterior = (array) $solicitud;

            // Actualizar solicitud
            $updateData = array_filter([
                'estatus'       => $request->estatus,
                'observaciones' => $request->observaciones,
            ], fn($v) => !is_null($v));

            if (!empty($updateData)) {
                DB::table('solicitud_residencia')->where('id_solicitud', $id)->update($updateData);
            }

            // Si se aprueba y viene datos de proyecto → crear o actualizar proyecto
            if ($request->filled('id_empresa') && $request->filled('nombre_proyecto')) {
                $proyectoExistente = DB::table('proyecto_residencia')
                    ->where('id_solicitud', $id)
                    ->first();

                $proyectoData = array_filter([
                    'id_solicitud'       => $id,
                    'id_empresa'         => $request->id_empresa,
                    'id_asesor_externo'  => $request->id_asesor_externo,
                    'id_docente_asesor'  => $request->id_docente_asesor,
                    'nombre_proyecto'    => $request->nombre_proyecto,
                    'descripcion'        => $request->descripcion,
                    'fecha_inicio'       => $request->fecha_inicio,
                    'fecha_fin_estimada' => $request->fecha_fin_estimada,
                    'horas_requeridas'   => $request->horas_requeridas ?? 480,
                ], fn($v) => !is_null($v));

                if ($proyectoExistente) {
                    DB::table('proyecto_residencia')
                        ->where('id_proyecto', $proyectoExistente->id_proyecto)
                        ->update($proyectoData);
                } else {
                    DB::table('proyecto_residencia')->insert($proyectoData + ['horas_acumuladas' => 0]);
                }
            }

            BitacoraService::registrar('UPDATE', 'solicitud_residencia', $id, $anterior, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Solicitud actualizada correctamente.',
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // GET /api/residencias/elegibles
    // =========================================================================

    /**
     * Lista alumnos que cumplen el 80% de créditos y no tienen solicitud activa.
     * Útil para el formulario de nueva solicitud.
     *
     * Filtros: ?carrera_id=1&q=nombre
     */
    public function elegibles(Request $request)
    {
        try {
            $query = DB::table('alumno as a')
                ->join('persona as p',    'a.id_persona', '=', 'p.id_persona')
                ->join('carrera as c',    'a.id_carrera', '=', 'c.id_carrera')
                ->join('kardex as k',     'k.id_alumno',  '=', 'a.id_alumno')
                ->join('plan_estudio as pe', function ($j) {
                    $j->on('pe.id_carrera', '=', 'a.id_carrera')
                      ->where('pe.estatus', 1);
                })
                ->where('a.estatus', 'Activo')
                ->whereRaw('k.creditos_acumulados >= ROUND(pe.total_creditos * ?)', [self::PORCENTAJE_MINIMO])
                ->whereNotExists(function ($sub) {
                    $sub->from('solicitud_residencia as sr')
                        ->whereColumn('sr.id_alumno', 'a.id_alumno')
                        ->whereIn('sr.estatus', ['Pendiente', 'Aprobada', 'En curso']);
                })
                ->select(
                    'a.id_alumno',
                    'a.numero_control',
                    DB::raw("CONCAT(p.nombre,' ',p.apellido_paterno,' ',COALESCE(p.apellido_materno,'')) as nombre_completo"),
                    'c.nombre as carrera',
                    'a.semestre_actual',
                    'k.creditos_acumulados',
                    'k.promedio_general',
                    DB::raw('ROUND(pe.total_creditos * ' . self::PORCENTAJE_MINIMO . ') as creditos_requeridos'),
                    DB::raw('ROUND((k.creditos_acumulados / pe.total_creditos) * 100, 1) as porcentaje_avance')
                );

            if ($request->filled('carrera_id')) {
                $query->where('a.id_carrera', $request->carrera_id);
            }

            if ($request->filled('q')) {
                $t = '%' . $request->q . '%';
                $query->where(function ($w) use ($t) {
                    $w->where('p.nombre', 'LIKE', $t)
                      ->orWhere('p.apellido_paterno', 'LIKE', $t)
                      ->orWhere('a.numero_control', 'LIKE', $t);
                });
            }

            $alumnos = $query->orderBy('p.apellido_paterno')->get();

            return response()->json([
                'total'   => $alumnos->count(),
                'alumnos' => $alumnos,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // GET /api/residencias/{id}/reportes
    // =========================================================================

    /**
     * Lista los reportes parciales de una residencia.
     */
    public function reportes(int $id)
    {
        try {
            $proyecto = DB::table('proyecto_residencia')
                ->where('id_solicitud', $id)
                ->first();

            if (!$proyecto) {
                return response()->json(['error' => 'No hay proyecto registrado para esta solicitud.'], 404);
            }

            $reportes = DB::table('reporte_residencia')
                ->where('id_proyecto', $proyecto->id_proyecto)
                ->orderBy('numero_reporte')
                ->get();

            return response()->json([
                'id_proyecto'      => $proyecto->id_proyecto,
                'horas_requeridas' => $proyecto->horas_requeridas,
                'horas_acumuladas' => $proyecto->horas_acumuladas,
                'reportes'         => $reportes,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // POST /api/residencias/{id}/reportes
    // =========================================================================

    /**
     * Registra o actualiza un reporte parcial.
     *
     * Body: { numero_reporte, fecha_entrega?, horas_periodo, calificacion?, observaciones?, estatus? }
     */
    public function guardarReporte(Request $request, int $id)
    {
        $request->validate([
            'numero_reporte' => 'required|integer|min:1|max:10',
            'horas_periodo'  => 'required|integer|min:0',
            'calificacion'   => 'nullable|numeric|min:0|max:100',
            'estatus'        => 'nullable|in:Pendiente,Entregado,Revisado,Aceptado,Rechazado',
        ]);

        try {
            $proyecto = DB::table('proyecto_residencia')
                ->where('id_solicitud', $id)
                ->first();

            if (!$proyecto) {
                return response()->json(['error' => 'No hay proyecto registrado para esta solicitud.'], 404);
            }

            $reporte = DB::table('reporte_residencia')
                ->where('id_proyecto', $proyecto->id_proyecto)
                ->where('numero_reporte', $request->numero_reporte)
                ->first();

            $datos = [
                'fecha_entrega'  => $request->fecha_entrega ?? now()->toDateString(),
                'horas_periodo'  => $request->horas_periodo,
                'calificacion'   => $request->calificacion,
                'observaciones'  => $request->observaciones,
                'estatus'        => $request->estatus ?? 'Entregado',
            ];

            if ($reporte) {
                DB::table('reporte_residencia')
                    ->where('id_reporte', $reporte->id_reporte)
                    ->update($datos);
            } else {
                DB::table('reporte_residencia')->insert(
                    $datos + ['id_proyecto' => $proyecto->id_proyecto, 'numero_reporte' => $request->numero_reporte]
                );
            }

            // Actualizar horas acumuladas del proyecto
            $horasTotal = DB::table('reporte_residencia')
                ->where('id_proyecto', $proyecto->id_proyecto)
                ->whereIn('estatus', ['Aceptado', 'Revisado', 'Entregado'])
                ->sum('horas_periodo');

            DB::table('proyecto_residencia')
                ->where('id_proyecto', $proyecto->id_proyecto)
                ->update(['horas_acumuladas' => $horasTotal]);

            return response()->json([
                'success'          => true,
                'message'          => 'Reporte guardado correctamente.',
                'horas_acumuladas' => $horasTotal,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // POST /api/residencias/{id}/evaluacion
    // =========================================================================

    /**
     * Registra la evaluación final de la residencia.
     * Si la calificación es aprobatoria (≥70), actualiza el kardex del alumno.
     *
     * Body: {
     *   calificacion_asesor_interno,
     *   calificacion_asesor_externo,
     *   carta_liberacion,     // boolean
     *   observaciones?
     * }
     */
    public function guardarEvaluacion(Request $request, int $id)
    {
        $request->validate([
            'calificacion_asesor_interno' => 'required|numeric|min:0|max:100',
            'calificacion_asesor_externo' => 'required|numeric|min:0|max:100',
            'carta_liberacion'            => 'required|boolean',
        ]);

        try {
            $solicitud = DB::table('solicitud_residencia')->where('id_solicitud', $id)->first();

            if (!$solicitud) {
                return response()->json(['error' => 'Solicitud no encontrada'], 404);
            }

            $proyecto = DB::table('proyecto_residencia')
                ->where('id_solicitud', $id)
                ->first();

            if (!$proyecto) {
                return response()->json(['error' => 'No hay proyecto registrado para esta solicitud.'], 404);
            }

            $calFinal = round(
                ($request->calificacion_asesor_interno + $request->calificacion_asesor_externo) / 2,
                2
            );

            // Crear o actualizar evaluación
            $evaluacionExistente = DB::table('evaluacion_residencia')
                ->where('id_proyecto', $proyecto->id_proyecto)
                ->first();

            $datosEval = [
                'calificacion_asesor_interno' => $request->calificacion_asesor_interno,
                'calificacion_asesor_externo' => $request->calificacion_asesor_externo,
                'calificacion_final'          => $calFinal,
                'fecha_evaluacion'            => now()->toDateString(),
                'carta_liberacion'            => $request->carta_liberacion ? 1 : 0,
                'observaciones'               => $request->observaciones,
            ];

            if ($evaluacionExistente) {
                DB::table('evaluacion_residencia')
                    ->where('id_evaluacion', $evaluacionExistente->id_evaluacion)
                    ->update($datosEval);
            } else {
                DB::table('evaluacion_residencia')->insert(
                    $datosEval + ['id_proyecto' => $proyecto->id_proyecto]
                );
            }

            // Actualizar estatus de la solicitud
            $nuevoEstatus = $calFinal >= 70 ? 'Concluida' : 'No acreditada';
            DB::table('solicitud_residencia')
                ->where('id_solicitud', $id)
                ->update(['estatus' => $nuevoEstatus]);

            // Si aprobó → registrar en detalle_kardex y actualizar créditos
            if ($calFinal >= 70) {
                $idMateria = DB::table('materia')->where('clave', 'RES-001')->value('id_materia');

                if ($idMateria) {
                    $kardex = DB::table('kardex')
                        ->where('id_alumno', $solicitud->id_alumno)
                        ->first();

                    if ($kardex) {
                        // Evitar duplicado
                        $yaRegistrada = DB::table('detalle_kardex')
                            ->where('id_kardex', $kardex->id_kardex)
                            ->where('id_materia', $idMateria)
                            ->exists();

                        if (!$yaRegistrada) {
                            $creditos = DB::table('materia')
                                ->where('id_materia', $idMateria)
                                ->value('creditos');

                            DB::table('detalle_kardex')->insert([
                                'id_kardex'        => $kardex->id_kardex,
                                'id_materia'       => $idMateria,
                                'calificacion_final' => $calFinal,
                                'estado'           => 'Acreditada',
                                'fecha_registro'   => now(),
                            ]);

                            DB::table('kardex')
                                ->where('id_kardex', $kardex->id_kardex)
                                ->update([
                                    'creditos_acumulados' => $kardex->creditos_acumulados + $creditos,
                                ]);
                        }
                    }
                }
            }

            BitacoraService::registrar('INSERT', 'evaluacion_residencia', $proyecto->id_proyecto, [], $datosEval);

            return response()->json([
                'success'            => true,
                'message'            => 'Evaluación registrada. Residencia marcada como: ' . $nuevoEstatus,
                'calificacion_final' => $calFinal,
                'estatus'            => $nuevoEstatus,
                'kardex_actualizado' => $calFinal >= 70,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // GET /api/empresas-residencia
    // =========================================================================

    /**
     * Lista empresas disponibles para asignar a proyectos.
     * Incluye sus asesores externos.
     */
    public function empresas()
    {
        try {
            $empresas = DB::table('empresa_residencia')
                ->where('estatus', 1)
                ->orderBy('nombre')
                ->get()
                ->map(function ($e) {
                    $asesores = DB::table('asesor_externo')
                        ->where('id_empresa', $e->id_empresa)
                        ->select('id_asesor_externo', 'nombre', 'puesto', 'correo')
                        ->get();

                    return [
                        'id_empresa'      => $e->id_empresa,
                        'nombre'          => $e->nombre,
                        'giro'            => $e->giro,
                        'ciudad'          => $e->ciudad,
                        'correo_contacto' => $e->correo_contacto,
                        'asesores'        => $asesores,
                    ];
                });

            return response()->json($empresas);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // Helper
    // =========================================================================

    private function formatearResidencia(object $r): array
    {
        return [
            'id_solicitud'        => $r->id_solicitud,
            'estatus'             => $r->estatus,
            'fecha_solicitud'     => $r->fecha_solicitud,
            'creditos_acumulados' => $r->creditos_acumulados,
            'creditos_requeridos' => $r->creditos_requeridos,
            'observaciones'       => $r->observaciones,
            'alumno' => [
                'id_alumno'      => $r->id_alumno,
                'numero_control' => $r->numero_control,
                'nombre'         => trim($r->nombre_alumno),
                'carrera'        => $r->carrera,
                'semestre'       => $r->semestre_actual,
            ],
            'periodo'   => $r->periodo,
            'proyecto'  => $r->id_proyecto ? [
                'id_proyecto'        => $r->id_proyecto,
                'nombre'             => $r->nombre_proyecto,
                'empresa'            => $r->empresa,
                'asesor_interno'     => trim($r->asesor_interno),
                'horas_requeridas'   => $r->horas_requeridas,
                'horas_acumuladas'   => $r->horas_acumuladas,
                'fecha_inicio'       => $r->fecha_inicio,
                'fecha_fin_estimada' => $r->fecha_fin_estimada,
            ] : null,
        ];
    }
}
