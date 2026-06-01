<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alumno;
use App\Models\Persona;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

// Para exportación PDF
use Barryvdh\DomPDF\Facade\Pdf;

class AlumnoController extends Controller
{
    // =====================================================================
    //  HELPERS
    // =====================================================================

    /**
     * Query base que siempre retorna los campos necesarios para la vista.
     * Se reutiliza en index(), kpis(), exportCsv() y exportPdf().
     */
    private function queryAlumnos(): \Illuminate\Database\Query\Builder
    {
        return DB::table('alumno as a')
            ->join('persona as p',          'a.id_persona',        '=', 'p.id_persona')
            ->join('carrera as c',          'a.id_carrera',        '=', 'c.id_carrera')
            ->join('estatus_alumno as ea',  'a.id_estatus_alumno', '=', 'ea.id_estatus_alumno')
            // especialidad viene de docente (si el alumno también es docente) o de otro lado;
            // como en este sistema no hay campo especialidad en alumno, lo exponemos desde carrera
            // si en el futuro se agrega a alumno, sólo se cambia aquí.
            ->select(
                'a.id_alumno',
                'a.numero_control',
                'a.id_carrera',
                'a.id_persona',
                'a.semestre_actual',
                'a.fecha_ingreso',
                'a.estatus',
                'a.id_estatus_alumno',
                'ea.nombre as estatus_nombre',
                'c.nombre as nombre_carrera',
                DB::raw("CONCAT(p.nombre, ' ', p.apellido_paterno, ' ', COALESCE(p.apellido_materno,'')) as nombre_completo"),
                'p.nombre as p_nombre',
                'p.apellido_paterno',
                'p.apellido_materno',
                'p.curp',
                'p.fecha_nacimiento',
                'p.id_genero'
            );
    }

    /**
     * Aplica los filtros comunes de la vista (carrera, semestre, estatus, búsqueda).
     */
    private function aplicarFiltros(\Illuminate\Database\Query\Builder $q, Request $request): \Illuminate\Database\Query\Builder
    {
        if ($request->filled('id_carrera')) {
            $q->where('a.id_carrera', $request->id_carrera);
        }
        if ($request->filled('semestre')) {
            $q->where('a.semestre_actual', $request->semestre);
        }
        if ($request->filled('id_estatus_alumno')) {
            $q->where('a.id_estatus_alumno', $request->id_estatus_alumno);
        }
        if ($request->filled('busqueda')) {
            $b = '%' . $request->busqueda . '%';
            $q->where(function ($sub) use ($b) {
                $sub->where('a.numero_control', 'like', $b)
                    ->orWhereRaw("CONCAT(p.nombre, ' ', p.apellido_paterno, ' ', COALESCE(p.apellido_materno,'')) like ?", [$b]);
            });
        }
        return $q;
    }

    // =====================================================================
    //  LISTADO PRINCIPAL  GET /api/alumnos-crud
    // =====================================================================

    public function index(Request $request)
    {
        try {
            $q = $this->queryAlumnos();
            $q = $this->aplicarFiltros($q, $request);
            $alumnos = $q->orderBy('p.apellido_paterno')->get();

            return response()->json($alumnos->map(fn($a) => $this->formatearAlumno($a)));
        } catch (\Exception $e) {
            Log::error("Error index alumnos: " . $e->getMessage());
            return response()->json(['error' => 'Error al cargar alumnos'], 500);
        }
    }

    /**
     * Convierte una fila plana (query builder) al formato que espera el frontend.
     */
    private function formatearAlumno(object $a): array
    {
        return [
            'id_alumno'         => $a->id_alumno,
            'numero_control'    => $a->numero_control,
            'id_carrera'        => $a->id_carrera,
            'id_persona'        => $a->id_persona,
            'semestre_actual'   => $a->semestre_actual,
            'fecha_ingreso'     => $a->fecha_ingreso,
            'id_estatus_alumno' => $a->id_estatus_alumno,
            'estatus'           => $a->estatus_nombre ?? $a->estatus ?? 'Activo',
            // nombre aplanado para búsqueda / tabla
            'nombre'            => trim($a->nombre_completo ?? ''),
            // objeto carrera compatible con alumno.carrera?.nombre_carrera en el Vue
            'carrera' => [
                'id_carrera'     => $a->id_carrera,
                'nombre_carrera' => $a->nombre_carrera ?? '',
                'nombre'         => $a->nombre_carrera ?? '',
            ],
            // objeto persona para la ficha de detalle
            'persona' => [
                'id_persona'       => $a->id_persona,
                'nombre'           => $a->p_nombre,
                'apellido_paterno' => $a->apellido_paterno,
                'apellido_materno' => $a->apellido_materno,
                'curp'             => $a->curp,
                'fecha_nacimiento' => $a->fecha_nacimiento,
                'id_genero'        => $a->id_genero,
            ],
        ];
    }

    // =====================================================================
    //  KPIs  GET /api/alumnos/kpis
    // =====================================================================

    public function kpis()
    {
        try {
            $totales = DB::table('alumno as a')
                ->join('estatus_alumno as ea', 'a.id_estatus_alumno', '=', 'ea.id_estatus_alumno')
                ->select('ea.nombre as estatus', DB::raw('COUNT(*) as total'))
                ->groupBy('ea.nombre')
                ->get()
                ->keyBy('estatus');

            return response()->json([
                'total'           => DB::table('alumno')->count(),
                'activos'         => $totales['Activo']->total          ?? 0,
                'baja_temporal'   => $totales['Baja Temporal']->total   ?? 0,
                'baja_definitiva' => $totales['Baja Definitiva']->total ?? 0,
                'egresados'       => $totales['Egresado']->total        ?? 0,
                'titulados'       => $totales['Titulado']->total        ?? 0,
            ]);
        } catch (\Exception $e) {
            Log::error("Error kpis alumnos: " . $e->getMessage());
            return response()->json(['error' => 'Error al calcular KPIs'], 500);
        }
    }

    // =====================================================================
    //  CATÁLOGOS  GET /api/alumnos/catalogos
    // =====================================================================

    public function catalogos()
    {
        try {
            return response()->json([
                'generos' => DB::table('genero')
                    ->select('id_genero', 'nombre_genero')
                    ->get(),

                'carreras' => DB::table('carrera')
                    ->select('id_carrera', 'nombre')
                    ->where('estatus', true)
                    ->orderBy('nombre')
                    ->get(),

                'estatus_alumno' => DB::table('estatus_alumno')
                    ->select('id_estatus_alumno', 'nombre')
                    ->orderBy('id_estatus_alumno')
                    ->get(),

                // Especialidades: se obtienen de las carreras activas;
                // si en el futuro hay tabla propia, sólo se cambia aquí.
                'especialidades' => DB::table('carrera')
                    ->select('id_carrera as id', 'nombre')
                    ->where('estatus', true)
                    ->orderBy('nombre')
                    ->get(),

                'niveles' => DB::table('nivel_carrera')->get(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // =====================================================================
    //  CREAR  POST /api/alumnos
    // =====================================================================

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'numero_control'    => 'required|string|unique:alumno,numero_control',
                'nombre'            => 'required|string',
                'apellido_paterno'  => 'required|string',
                'genero'            => 'required|in:Masculino,Femenino,Otro',
                'id_carrera'        => 'required|integer|exists:carrera,id_carrera',
                'semestre_actual'   => 'required|integer|between:1,8',
                'id_estatus_alumno' => 'required|integer|exists:estatus_alumno,id_estatus_alumno',
                'fecha_ingreso'     => 'required|date',
            ]);

            $idGenero = DB::table('genero')
                ->where('nombre_genero', $request->genero)
                ->value('id_genero');

            $persona = Persona::create([
                'nombre'           => $request->nombre,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno ?? null,
                'id_genero'        => $idGenero,
                'curp'             => $request->curp ?? null,
                'fecha_nacimiento' => $request->fecha_nacimiento ?? null,
            ]);

            $estatusNombre = DB::table('estatus_alumno')
                ->where('id_estatus_alumno', $request->id_estatus_alumno)
                ->value('nombre') ?? 'Activo';

            $alumno = Alumno::create([
                'numero_control'    => $request->numero_control,
                'id_persona'        => $persona->id_persona,
                'id_carrera'        => $request->id_carrera,
                'semestre_actual'   => $request->semestre_actual,
                'estatus'           => $estatusNombre,
                'id_estatus_alumno' => $request->id_estatus_alumno,
                'fecha_ingreso'     => $request->fecha_ingreso,
            ]);

            DB::commit();

            BitacoraService::registrar('INSERT', 'alumno', $alumno->id_alumno, [], [
                'numero_control' => $request->numero_control,
                'id_carrera'     => $request->id_carrera,
                'semestre'       => $request->semestre_actual,
            ]);

            return response()->json([
                'message' => 'Alumno registrado correctamente',
                'data'    => $alumno->load(['persona', 'carrera', 'estatusAlumno'])
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json(['error' => 'Datos inválidos', 'detalle' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al crear alumno: " . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor', 'detalle' => $e->getMessage()], 500);
        }
    }

    // =====================================================================
    //  ACTUALIZAR  PUT /api/alumnos/{id}
    // =====================================================================

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            Log::info("Actualizando alumno ID: {$id}", $request->all());

            $alumno = Alumno::with('persona')->findOrFail($id);

            // Actualizar datos de persona
            if ($alumno->persona && $request->filled('nombre')) {
                // Soporte para enviar nombre/apellido por separado O como nombre completo
                if ($request->filled('apellido_paterno')) {
                    $alumno->persona->update([
                        'nombre'           => $request->nombre,
                        'apellido_paterno' => $request->apellido_paterno,
                        'apellido_materno' => $request->apellido_materno ?? $alumno->persona->apellido_materno,
                    ]);
                } else {
                    // Compatibilidad: el Vue envía el nombre completo en un solo campo
                    $partes            = explode(' ', trim($request->nombre));
                    $nombre           = $partes[0] ?? '';
                    $apellido_paterno = $partes[1] ?? '';
                    $apellido_materno = implode(' ', array_slice($partes, 2));
                    $alumno->persona->update([
                        'nombre'           => $nombre,
                        'apellido_paterno' => $apellido_paterno,
                        'apellido_materno' => $apellido_materno ?: $alumno->persona->apellido_materno,
                    ]);
                }
            }

            // Resolver nombre de estatus si se envía id_estatus_alumno
            $estatusNombre = $alumno->estatus;
            if ($request->filled('id_estatus_alumno')) {
                $estatusNombre = DB::table('estatus_alumno')
                    ->where('id_estatus_alumno', $request->id_estatus_alumno)
                    ->value('nombre') ?? $alumno->estatus;
            }

            $alumno->update([
                'id_carrera'        => $request->id_carrera        ?? $alumno->id_carrera,
                'semestre_actual'   => $request->semestre_actual   ?? $alumno->semestre_actual,
                'estatus'           => $estatusNombre,
                'id_estatus_alumno' => $request->id_estatus_alumno ?? $alumno->id_estatus_alumno,
            ]);

            DB::commit();

            BitacoraService::registrar(
                'UPDATE', 'alumno', $id,
                ['id_carrera' => $alumno->getOriginal('id_carrera'), 'semestre_actual' => $alumno->getOriginal('semestre_actual'), 'estatus' => $alumno->getOriginal('estatus')],
                ['id_carrera' => $request->id_carrera, 'semestre_actual' => $request->semestre_actual, 'estatus' => $estatusNombre]
            );

            $alumno->load(['persona', 'carrera', 'estatusAlumno']);
            $alumno->estatus = $alumno->estatusAlumno?->nombre ?? $estatusNombre;

            return response()->json([
                'message' => 'Alumno actualizado con éxito',
                'data'    => $alumno
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al actualizar alumno ID {$id}: " . $e->getMessage());
            return response()->json(['error' => 'No se pudo actualizar el alumno', 'detalle' => $e->getMessage()], 500);
        }
    }

    // =====================================================================
    //  ELIMINAR  DELETE /api/alumnos/{id}
    // =====================================================================

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $alumno    = Alumno::with('persona')->findOrFail($id);
            $idPersona = $alumno->id_persona;

            // 1. Kardex
            $kardexIds = DB::table('kardex')->where('id_alumno', $id)->pluck('id_kardex');
            if ($kardexIds->isNotEmpty()) {
                DB::table('detalle_kardex')->whereIn('id_kardex', $kardexIds)->delete();
                DB::table('kardex')->whereIn('id_kardex', $kardexIds)->delete();
            }

            // 2. Calificaciones e inscripciones
            $inscripcionIds = DB::table('inscripcion')->where('id_alumno', $id)->pluck('id_inscripcion');
            if ($inscripcionIds->isNotEmpty()) {
                DB::table('calificacion')->whereIn('id_inscripcion', $inscripcionIds)->delete();
            }
            DB::table('inscripcion')->where('id_alumno', $id)->delete();

            // 3. Participación en eventos
            DB::table('participacion_evento')->where('id_alumno', $id)->delete();

            // 4. Alumno
            DB::table('alumno')->where('id_alumno', $id)->delete();

            // 5. Contacto de persona
            DB::table('persona_correo')->where('id_persona', $idPersona)->delete();
            DB::table('persona_telefono')->where('id_persona', $idPersona)->delete();
            DB::table('persona_direccion')->where('id_persona', $idPersona)->delete();

            // 6. Comité académico
            $solicitudIds = DB::table('solicitud_comite')->where('id_persona', $idPersona)->pluck('id_solicitud');
            if ($solicitudIds->isNotEmpty()) {
                DB::table('resolucion_comite')->whereIn('id_solicitud', $solicitudIds)->delete();
                DB::table('solicitud_comite')->whereIn('id_solicitud', $solicitudIds)->delete();
            }

            // 7. Usuarios y bitácora
            $usuarioIds = DB::table('usuario')->where('id_persona', $idPersona)->pluck('id_usuario');
            if ($usuarioIds->isNotEmpty()) {
                DB::table('bitacora')->whereIn('id_usuario', $usuarioIds)->delete();
                DB::table('usuario_rol')->whereIn('id_usuario', $usuarioIds)->delete();
                DB::table('usuario')->whereIn('id_usuario', $usuarioIds)->delete();
            }

            // 8. Empleado / docente
            $empleadoIds = DB::table('empleado')->where('id_persona', $idPersona)->pluck('id_empleado');
            if ($empleadoIds->isNotEmpty()) {
                $docenteIds = DB::table('docente')->whereIn('id_empleado', $empleadoIds)->pluck('id_docente');
                if ($docenteIds->isNotEmpty()) {
                    DB::table('grupo')->whereIn('id_docente', $docenteIds)->update(['id_docente' => null]);
                }
                DB::table('adscripcion')->whereIn('id_empleado', $empleadoIds)->delete();
                DB::table('docente')->whereIn('id_empleado', $empleadoIds)->delete();
                DB::table('empleado')->whereIn('id_empleado', $empleadoIds)->delete();
            }

            // 9. Persona
            DB::table('persona')->where('id_persona', $idPersona)->delete();

            DB::commit();

            BitacoraService::registrar('DELETE', 'alumno', $id, [
                'id_persona'     => $idPersona,
                'numero_control' => $alumno->numero_control,
            ]);

            return response()->json(['message' => 'Alumno eliminado correctamente']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("ERROR DELETE ALUMNO ID {$id}: " . $e->getMessage());
            return response()->json(['error' => 'No se pudo eliminar al alumno.', 'detalle' => $e->getMessage()], 500);
        }
    }

    // =====================================================================
    //  HORARIO  GET /api/horario/{numero_control}
    // =====================================================================

    public function horario($numero_control)
    {
        try {
            $alumno = Alumno::where('numero_control', $numero_control)->firstOrFail();

            $horario = DB::table('inscripcion as i')
                ->join('grupo as g',        'i.id_grupo',    '=', 'g.id_grupo')
                ->join('materia as m',      'g.id_materia',  '=', 'm.id_materia')
                ->leftJoin('docente as d',  'g.id_docente',  '=', 'd.id_docente')
                ->leftJoin('empleado as e', 'd.id_empleado', '=', 'e.id_empleado')
                ->leftJoin('persona as p',  'e.id_persona',  '=', 'p.id_persona')
                ->where('i.id_alumno', $alumno->id_alumno)
                ->select(
                    'g.id_grupo as id',
                    'm.nombre as nombre',
                    'g.dia',
                    'g.hora_inicio',
                    'g.hora_fin',
                    'g.id_aula',
                    DB::raw("CONCAT(COALESCE(p.nombre,''), ' ', COALESCE(p.apellido_paterno,'')) as docente")
                )
                ->orderBy('g.hora_inicio')
                ->get();

            return response()->json($horario);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Alumno no encontrado'], 404);
        } catch (\Exception $e) {
            Log::error("Error horario alumno {$numero_control}: " . $e->getMessage());
            return response()->json(['error' => 'Error al cargar horario', 'detalle' => $e->getMessage()], 500);
        }
    }

    // =====================================================================
    //  EXPORTAR CSV  GET /api/alumnos/exportar-csv
    // =====================================================================

    public function exportarCsv(Request $request)
    {
        try {
            $q       = $this->queryAlumnos();
            $q       = $this->aplicarFiltros($q, $request);
            $alumnos = $q->orderBy('p.apellido_paterno')->get();

            $filename = 'alumnos_' . now()->format('Ymd_His') . '.csv';

            $headers = [
                'Content-Type'        => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function () use ($alumnos) {
                $handle = fopen('php://output', 'w');
                // BOM para Excel en español
                fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

                // Encabezado
                fputcsv($handle, [
                    'No. Control', 'Nombre', 'Apellido Paterno', 'Apellido Materno',
                    'Carrera', 'Semestre', 'Estatus', 'Fecha Ingreso', 'CURP'
                ]);

                foreach ($alumnos as $a) {
                    fputcsv($handle, [
                        $a->numero_control,
                        $a->p_nombre,
                        $a->apellido_paterno,
                        $a->apellido_materno,
                        $a->nombre_carrera,
                        $a->semestre_actual,
                        $a->estatus_nombre,
                        $a->fecha_ingreso,
                        $a->curp,
                    ]);
                }
                fclose($handle);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error("Error exportar CSV alumnos: " . $e->getMessage());
            return response()->json(['error' => 'Error al generar CSV'], 500);
        }
    }

    // =====================================================================
    //  EXPORTAR PDF  GET /api/alumnos/exportar-pdf
    //  Requiere: barryvdh/laravel-dompdf  (composer require barryvdh/laravel-dompdf)
    // =====================================================================

    public function exportarPdf(Request $request)
    {
        try {
            $q       = $this->queryAlumnos();
            $q       = $this->aplicarFiltros($q, $request);
            $alumnos = $q->orderBy('p.apellido_paterno')->get()->map(fn($a) => $this->formatearAlumno($a));

            // KPIs para el encabezado del PDF
            $kpis = [
                'total'    => $alumnos->count(),
                'activos'  => $alumnos->where('estatus', 'Activo')->count(),
                'bajas'    => $alumnos->whereIn('estatus', ['Baja Temporal', 'Baja Definitiva'])->count(),
                'egresados'=> $alumnos->whereIn('estatus', ['Egresado', 'Titulado'])->count(),
            ];

            $filtrosAplicados = array_filter([
                'Carrera'  => $request->filled('id_carrera')
                    ? DB::table('carrera')->where('id_carrera', $request->id_carrera)->value('nombre')
                    : null,
                'Semestre' => $request->filled('semestre') ? $request->semestre . '°' : null,
                'Estatus'  => $request->filled('id_estatus_alumno')
                    ? DB::table('estatus_alumno')->where('id_estatus_alumno', $request->id_estatus_alumno)->value('nombre')
                    : null,
            ]);

            $pdf = Pdf::loadView('pdf.alumnos', [
                'alumnos'          => $alumnos,
                'kpis'             => $kpis,
                'filtrosAplicados' => $filtrosAplicados,
                'fecha'            => now()->format('d/m/Y H:i'),
            ])->setPaper('letter', 'landscape');

            $filename = 'alumnos_' . now()->format('Ymd_His') . '.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error("Error exportar PDF alumnos: " . $e->getMessage());
            return response()->json(['error' => 'Error al generar PDF', 'detalle' => $e->getMessage()], 500);
        }
    }


    // =====================================================================
    //  EXPEDIENTE  GET /api/alumnos/{numero_control}/expediente
    //  Retorna todos los datos que necesita ExpedienteSE.vue
    //
    //  BD usada (migracion_alumno_campos_nuevos.sql):
    //    - alumno.nss, folio_subes, tipo_sangre, discapacidad, id_especialidad
    //    - tabla especialidad  (id_especialidad → nombre)
    //    - tabla contacto_emergencia_alumno  (FK a alumno, campo nombre_completo)
    //    - persona_correo.correo  (NO "email")
    //    - persona_direccion.direccion  (campo text, NO calle/colonia)
    // =====================================================================

    public function expediente($numero_control)
    {
        try {
            // ── Alumno + relaciones ───────────────────────────────────────
            $alumno = Alumno::with(['persona', 'carrera', 'estatusAlumno'])
                ->where('numero_control', $numero_control)
                ->firstOrFail();

            $persona   = $alumno->persona;
            $idPersona = $persona->id_persona;
            $idAlumno  = $alumno->id_alumno;

            // ── Contacto personal ─────────────────────────────────────────
            $telefono = DB::table('persona_telefono')
                ->where('id_persona', $idPersona)
                ->value('telefono');

            // OJO: columna se llama 'correo', no 'email'
            $correo = DB::table('persona_correo')
                ->where('id_persona', $idPersona)
                ->value('correo');

            // persona_direccion tiene un solo campo texto 'direccion'
            $direccion = DB::table('persona_direccion')
                ->where('id_persona', $idPersona)
                ->value('direccion');

            // ── Especialidad (tabla propia, FK desde alumno.id_especialidad) ──
            $especialidad = null;
            if ($alumno->id_especialidad) {
                $especialidad = DB::table('especialidad')
                    ->where('id_especialidad', $alumno->id_especialidad)
                    ->select('id_especialidad', 'nombre', 'descripcion')
                    ->first();
            }

            // ── Plan de estudios (activo de la carrera) ───────────────────
            $planEstudio = DB::table('plan_estudio')
                ->where('id_carrera', $alumno->id_carrera)
                ->where('estatus', 1)
                ->orderByDesc('anio_vigencia')
                ->first();

            // Género: se lee directo por Query Builder porque el modelo Persona
            // no tiene definida la relación genero() — no modificar Persona.php
            $generoNombre = DB::table('genero')
                ->where('id_genero', $persona->id_genero)
                ->value('nombre_genero');

            // ── Contacto de emergencia (tabla contacto_emergencia_alumno) ──
            // Campo: nombre_completo (NO 'nombre'), más telefono_alt y es_principal
            $contactoEmergencia = DB::table('contacto_emergencia_alumno')
                ->where('id_alumno', $idAlumno)
                ->where('es_principal', 1)
                ->first();

            // ── Respuesta ─────────────────────────────────────────────────
            $response = [
                'id_alumno'         => $alumno->id_alumno,
                'numero_control'    => $alumno->numero_control,
                'semestre_actual'   => $alumno->semestre_actual,
                'fecha_ingreso'     => $alumno->fecha_ingreso,
                'estatus'           => $alumno->estatusAlumno?->nombre ?? $alumno->estatus,
                'id_estatus_alumno' => $alumno->id_estatus_alumno,

                // Datos personales
                'nombre' => trim(
                    $persona->nombre . ' ' .
                    $persona->apellido_paterno . ' ' .
                    ($persona->apellido_materno ?? '')
                ),
                'curp'             => $persona->curp,
                'fecha_nacimiento' => $persona->fecha_nacimiento,
                'genero'           => $generoNombre,
                'estado_civil'     => $persona->estado_civil,
                'estado'           => $persona->estado,

                // Contacto
                'telefono'  => $telefono,
                'email'     => $correo,
                'direccion' => $direccion,

                // Datos académicos
                'carrera' => $alumno->carrera ? [
                    'id_carrera'     => $alumno->carrera->id_carrera,
                    'nombre_carrera' => $alumno->carrera->nombre,
                    'nombre'         => $alumno->carrera->nombre,
                ] : null,

                // Especialidad (objeto completo + nombre plano para el Vue)
                'id_especialidad' => $alumno->id_especialidad,
                'especialidad'    => $especialidad?->nombre,
                'especialidad_obj'=> $especialidad,

                'plan_estudios' => $planEstudio?->nombre_plan,
                'modalidad'     => 'ESCOLARIZADO',

                // Seguro y SUBES
                'nss'         => $alumno->nss,
                'folio_subes' => $alumno->folio_subes,
                'tipo_sangre' => $alumno->tipo_sangre,
                'discapacidad'=> $alumno->discapacidad,

                // Contacto de emergencia
                // Vue usa .nombre, .parentesco, .telefono — mapeamos nombre_completo → nombre
                'contacto_emergencia' => $contactoEmergencia ? [
                    'id_contacto'  => $contactoEmergencia->id_contacto,
                    'nombre'       => $contactoEmergencia->nombre_completo,
                    'parentesco'   => $contactoEmergencia->parentesco,
                    'telefono'     => $contactoEmergencia->telefono,
                    'telefono_alt' => $contactoEmergencia->telefono_alt,
                ] : null,
            ];

            return response()->json(['success' => true, 'data' => $response]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Alumno no encontrado'], 404);
        } catch (\Exception $e) {
            Log::error("Error expediente alumno {$numero_control}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al cargar expediente'], 500);
        }
    }

    // =====================================================================
    //  ACTUALIZAR EXPEDIENTE  PUT /api/alumnos/{numero_control}/expediente
    // =====================================================================

    public function actualizarExpediente(Request $request, $numero_control)
    {
        DB::beginTransaction();
        try {
            $alumno    = Alumno::with('persona')->where('numero_control', $numero_control)->firstOrFail();
            $persona   = $alumno->persona;
            $idPersona = $persona->id_persona;
            $idAlumno  = $alumno->id_alumno;

            // ── Persona: CURP, nacimiento, estado civil, estado ───────────
            $datosPersona = array_filter([
                'curp'             => $request->curp,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'estado_civil'     => $request->estado_civil,
                'estado'           => $request->estado,
            ], fn($v) => $v !== null);

            if (!empty($datosPersona)) {
                $persona->update($datosPersona);
            }

            // ── Teléfono, correo, dirección (upsert) ──────────────────────
            if ($request->filled('telefono')) {
                DB::table('persona_telefono')->updateOrInsert(
                    ['id_persona' => $idPersona],
                    ['telefono'   => $request->telefono]
                );
            }

            if ($request->filled('email')) {
                // columna 'correo', no 'email'
                DB::table('persona_correo')->updateOrInsert(
                    ['id_persona' => $idPersona],
                    ['correo'     => $request->email]
                );
            }

            if ($request->filled('direccion')) {
                DB::table('persona_direccion')->updateOrInsert(
                    ['id_persona' => $idPersona],
                    ['direccion'  => $request->direccion]
                );
            }

            // ── Alumno: campos de la migración ────────────────────────────
            // tipo_sangre es ENUM — solo se actualiza si el valor es válido
            $tiposSangre = ['A+','A-','B+','B-','AB+','AB-','O+','O-'];
            $datosAlumno = array_filter([
                'id_especialidad' => $request->id_especialidad,
                'nss'             => $request->nss,
                'folio_subes'     => $request->folio_subes,
                'tipo_sangre'     => in_array($request->tipo_sangre, $tiposSangre) ? $request->tipo_sangre : null,
                'discapacidad'    => $request->discapacidad,
            ], fn($v) => $v !== null);

            if (!empty($datosAlumno)) {
                $alumno->update($datosAlumno);
            }

            // ── Contacto de emergencia ────────────────────────────────────
            // Vue envía: { nombre, parentesco, telefono, telefono_alt }
            if ($request->filled('contacto_emergencia')) {
                $ce = $request->contacto_emergencia;
                DB::table('contacto_emergencia_alumno')->updateOrInsert(
                    ['id_alumno' => $idAlumno, 'es_principal' => 1],
                    [
                        'nombre_completo' => $ce['nombre']       ?? null,
                        'parentesco'      => $ce['parentesco']   ?? null,
                        'telefono'        => $ce['telefono']     ?? '',
                        'telefono_alt'    => $ce['telefono_alt'] ?? null,
                        'updated_at'      => now(),
                    ]
                );
            }

            DB::commit();

            BitacoraService::registrar('UPDATE', 'alumno', $idAlumno, [], [
                'accion'         => 'actualizar_expediente',
                'numero_control' => $numero_control,
            ]);

            return $this->expediente($numero_control);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Alumno no encontrado'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error actualizarExpediente {$numero_control}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar expediente', 'detalle' => $e->getMessage()], 500);
        }
    }

    // =====================================================================
    //  ESPECIALIDADES POR CARRERA
    //  GET /api/alumnos/especialidades?id_carrera={id}
    //  Catálogo para el select de especialidad al editar expediente
    // =====================================================================

    public function materias($id)
    {
        try {
            $materias = DB::table('inscripcion as i')
                ->join('grupo as g',   'i.id_grupo',   '=', 'g.id_grupo')
                ->join('materia as m', 'g.id_materia', '=', 'm.id_materia')
                ->join('periodo as p', 'g.id_periodo', '=', 'p.id_periodo')
                ->where('i.id_alumno', $id)
                ->select(
                    'i.id_inscripcion',
                    'm.id_materia',
                    'm.nombre as materia',
                    'm.creditos',
                    'p.nombre_periodo as periodo',
                    'i.estatus',
                    'g.id_grupo'
                )
                ->orderByDesc('p.id_periodo')
                ->get();

            return response()->json($materias);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function especialidades(Request $request)
    {
        try {
            $q = DB::table('especialidad')->where('estatus', 1);
            if ($request->filled('id_carrera')) {
                $q->where('id_carrera', $request->id_carrera);
            }
            return response()->json(
                $q->select('id_especialidad', 'id_carrera', 'nombre', 'descripcion')
                  ->orderBy('nombre')
                  ->get()
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
