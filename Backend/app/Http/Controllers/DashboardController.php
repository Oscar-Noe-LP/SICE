<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $alumnosActivosQuery = $this->alumnosConEstatus(['Activo', '1', 'true']);

            $alumnos = (clone $alumnosActivosQuery)->count();

            $inscripciones = DB::table('inscripcion')->count();

            $grupos = $this->gruposActivos()->count();

            $bajasTemporales = $this->alumnosConEstatus(['Baja Temporal'])->count();

            $bajasDefinitivas = $this->alumnosConEstatus(['Baja Definitiva'])->count();

            $promedio = DB::table('calificacion')->avg('calificacion');

            $carreras = (clone $alumnosActivosQuery)
                ->leftJoin('carrera as c', 'a.id_carrera', '=', 'c.id_carrera')
                ->select(
                    DB::raw("COALESCE(c.nombre, 'Sin carrera') as nombre"),
                    DB::raw('COUNT(*) as total')
                )
                ->groupBy('c.id_carrera', 'c.nombre')
                ->orderByDesc('total')
                ->get();

            $semestres = (clone $alumnosActivosQuery)
                ->select(
                    DB::raw('COALESCE(a.semestre_actual, 0) as semestre_actual'),
                    DB::raw('COUNT(*) as total')
                )
                ->groupBy('a.semestre_actual')
                ->orderBy('a.semestre_actual')
                ->get();

            return response()->json([
                'kpis' => [
                    'alumnos' => $alumnos,
                    'inscripciones' => $inscripciones,
                    'grupos' => $grupos,
                    'bajas_temporales' => $bajasTemporales,
                    'bajas_definitivas' => $bajasDefinitivas,
                    'promedio' => $promedio ? round($promedio, 2) : 0,
                ],

                'carreras' => $carreras,

                'semestres' => $semestres,

                // NUEVO
                'carreras_detalle' => $this->resumenCarreras(),

                'actividad_reciente' => $this->actividadReciente(),
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'error' => 'Error en servidor',
                'mensaje' => $e->getMessage(),
            ], 500);
        }
    }

    public function carreras()
    {
        try {
            $periodo  = DB::table('periodo')->where('activo', 1)->orderByDesc('id_periodo')->first();
            $carreras = $this->_carrerasKpis($periodo);
            $maxMat   = count($carreras) > 0 ? max(array_column($carreras, 'matriculas')) : 1;
            $maxMat   = $maxMat ?: 1;
            $carreraData = array_map(fn($c) => [
                'carrera'    => $c['nombre'],
                'total'      => $c['matriculas'],
                'porcentaje' => (int) round($c['matriculas'] / $maxMat * 100),
            ], $carreras);
            return response()->json([
                'carreras'    => $carreras,
                'carreraData' => $carreraData,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function semestres()
    {
        try {
            $semestres = $this->alumnosConEstatus(['Activo', '1', 'true'])
                ->select(
                    DB::raw('COALESCE(a.semestre_actual, 0) as semestre'),
                    DB::raw('COUNT(*) as cantidad')
                )
                ->groupBy('a.semestre_actual')
                ->orderBy('a.semestre_actual')
                ->get()
                ->map(fn($s) => ['semestre' => (string) $s->semestre, 'cantidad' => (int) $s->cantidad])
                ->values()
                ->toArray();
            return response()->json(['semestres' => $semestres]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function kpis()
    {
        try {
            $periodo      = DB::table('periodo')->where('activo', 1)->orderByDesc('id_periodo')->first();
            $totalAlumnos = $this->alumnosConEstatus(['Activo', '1', 'true'])->count();

            $alumnosInscritos = DB::table('inscripcion as i')
                ->join('grupo as g', 'i.id_grupo', '=', 'g.id_grupo')
                ->join('alumno as a', 'a.id_alumno', '=', 'i.id_alumno')
                ->where('g.id_periodo', $periodo->id_periodo ?? 0)
                ->where('a.estatus', 'Activo')
                ->where('i.estatus', '!=', 'Cancelada')
                ->distinct()
                ->count('i.id_alumno');

            $totalInscripciones = DB::table('inscripcion')->count();
            $conCalif = DB::table('inscripcion as i')
                ->join('calificacion as c', 'i.id_inscripcion', '=', 'c.id_inscripcion')
                ->distinct('i.id_inscripcion')
                ->count('i.id_inscripcion');
            $sinCalif = max(0, $totalInscripciones - $conCalif);
            $pct      = $totalInscripciones > 0 ? (int) round($conCalif / $totalInscripciones * 100) : 0;

            $gruposActivos   = DB::table('grupo')->where('estatus', 1)->count();
            $numCarreras     = DB::table('carrera')->where('estatus', 1)->count();
            $materiasActivas = DB::table('materia')->where('estatus', 1)->count();
            $adeudos         = Schema::hasTable('adeudo') ? DB::table('adeudo')->where('pagado', false)->count() : 0;

            $egresados        = $this->alumnosConEstatus(['Egresado'])->count();
            $titulados        = $this->alumnosConEstatus(['Titulado'])->count();
            $bajasTemporales  = $this->alumnosConEstatus(['Baja Temporal'])->count();
            $bajasDefinitivas = $this->alumnosConEstatus(['Baja Definitiva'])->count();

            $hoy          = now()->toDateString();
            $consultasHoy = DB::table('bitacora')->whereDate('fecha_hora', $hoy)->count();

            $nuevosAlumnos = 0;

            $carreras = $this->_carrerasKpis($periodo);

            $maxMat = count($carreras) > 0
                ? max(array_column($carreras, 'matriculas'))
                : 1;
            $maxMat = $maxMat ?: 1;

            $carreraData = array_map(fn($c) => [
                'carrera'    => $c['nombre'],
                'total'      => $c['matriculas'],
                'porcentaje' => (int) round($c['matriculas'] / $maxMat * 100),
            ], $carreras);

            $semestreData = $this->alumnosConEstatus(['Activo', '1', 'true'])
                ->select(
                    DB::raw('COALESCE(a.semestre_actual, 0) as semestre'),
                    DB::raw('COUNT(*) as cantidad')
                )
                ->groupBy('a.semestre_actual')
                ->orderBy('a.semestre_actual')
                ->get()
                ->map(fn($s) => ['semestre' => (string) $s->semestre, 'cantidad' => (int) $s->cantidad])
                ->values()
                ->toArray();

            return response()->json([
                'kpis' => [
                    'totalAlumnos'            => $totalAlumnos,
                    'alumnosInscritos'        => $alumnosInscritos,
                    'nuevosAlumnos'           => $nuevosAlumnos,
                    'inscripciones'           => $totalInscripciones,
                    'inscripcionesCompletas'  => $conCalif,
                    'inscripcionesPendientes' => $sinCalif,
                    'pctInscripciones'        => $pct,
                    'gruposActivos'           => $gruposActivos,
                    'numCarreras'             => $numCarreras,
                    'materiasActivas'         => $materiasActivas,
                    'adeudosPendientes'       => $adeudos,
                    'egresados'               => $egresados,
                    'titulados'               => $titulados,
                    'bajasTemporales'         => $bajasTemporales,
                    'bajasDefinitivas'        => $bajasDefinitivas,
                    'consultasHoy'            => $consultasHoy,
                    'periodoActivo'           => $periodo->nombre_periodo ?? 'Sin periodo',
                ],
                'carreras'     => $carreras,
                'carrera_data' => $carreraData,
                'semestre_data'=> $semestreData,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function _carrerasKpis(?object $periodo)
    {
        $carreras = DB::table('carrera')->where('estatus', 1)->get();
        $resultado = [];

        foreach ($carreras as $carrera) {
            $matriculas = DB::table('alumno')
                ->where('id_carrera', $carrera->id_carrera)
                ->whereIn('estatus', ['Activo', '1'])
                ->count();

            $grupos = $periodo
                ? DB::table('grupo as g')
                    ->join('inscripcion as i', 'g.id_grupo', '=', 'i.id_grupo')
                    ->join('alumno as a', 'i.id_alumno', '=', 'a.id_alumno')
                    ->where('a.id_carrera', $carrera->id_carrera)
                    ->where('g.id_periodo', $periodo->id_periodo)
                    ->where('g.estatus', 1)
                    ->distinct('g.id_grupo')
                    ->count('g.id_grupo')
                : 0;

            $irregulares = $periodo
                ? DB::table('alumno as a')
                    ->join('inscripcion as i', 'a.id_alumno', '=', 'i.id_alumno')
                    ->join('grupo as g', 'i.id_grupo', '=', 'g.id_grupo')
                    ->join('calificacion as c', 'i.id_inscripcion', '=', 'c.id_inscripcion')
                    ->where('a.id_carrera', $carrera->id_carrera)
                    ->where('g.id_periodo', $periodo->id_periodo)
                    ->where('c.calificacion', '<', 70)
                    ->distinct('a.id_alumno')
                    ->count('a.id_alumno')
                : 0;

            $resultado[] = [
                'id_carrera'  => $carrera->id_carrera,
                'nombre'      => $carrera->nombre,
                'grupos'      => $grupos,
                'matriculas'  => $matriculas,
                'regulares'   => max(0, $matriculas - $irregulares),
                'irregulares' => $irregulares,
            ];
        }

        return $resultado;
    }

    private function alumnosConEstatus(array $estatus)
    {
        $normalizados = array_map(
            fn ($valor) => mb_strtolower($valor),
            $estatus
        );

        $tieneEstatusCol = Schema::hasColumn('alumno', 'estatus');

        $tieneCatalogoEstatus = Schema::hasTable('estatus_alumno')
            && Schema::hasColumn('alumno', 'id_estatus_alumno');

        $query = DB::table('alumno as a');

        if ($tieneCatalogoEstatus) {
            $query->leftJoin(
                'estatus_alumno as ea',
                'a.id_estatus_alumno',
                '=',
                'ea.id_estatus_alumno'
            );
        }

        if (!$tieneEstatusCol && !$tieneCatalogoEstatus) {
            return $query;
        }

        return $query->where(function ($q) use (
            $normalizados,
            $tieneEstatusCol,
            $tieneCatalogoEstatus
        ) {

            if ($tieneEstatusCol) {
                $q->whereIn(
                    DB::raw('LOWER(CAST(a.estatus AS CHAR))'),
                    $normalizados
                );
            }

            if ($tieneCatalogoEstatus) {
                $q->orWhereIn(
                    DB::raw('LOWER(ea.nombre)'),
                    $normalizados
                );
            }
        });
    }

    private function gruposActivos()
    {
        $query = DB::table('grupo');

        if (Schema::hasColumn('grupo', 'estatus')) {
            $query->whereIn(
                DB::raw('LOWER(CAST(estatus AS CHAR))'),
                ['activo', '1', 'true']
            );
        }

        return $query;
    }

    private function resumenCarreras()
    {
        $periodoActual = DB::table('periodo')
            ->where('activo', 1)
            ->first();

        if (!$periodoActual) {
            return [];
        }

        $carreras = DB::table('carrera')
            ->where('estatus', 1)
            ->get();

        $resultado = [];

        foreach ($carreras as $carrera) {

            // Total alumnos activos
            $totalAlumnos = DB::table('alumno as a')
                ->where('a.id_carrera', $carrera->id_carrera)
                ->where(function ($q) {
                    $q->where('a.estatus', 'Activo')
                      ->orWhere('a.estatus', '1');
                })
                ->count();

            // Total grupos activos del periodo
            $totalGrupos = DB::table('grupo as g')
                ->join('inscripcion as i', 'g.id_grupo', '=', 'i.id_grupo')
                ->join('alumno as a', 'i.id_alumno', '=', 'a.id_alumno')
                ->where('a.id_carrera', $carrera->id_carrera)
                ->where('g.id_periodo', $periodoActual->id_periodo)
                ->where('g.estatus', 1)
                ->distinct('g.id_grupo')
                ->count('g.id_grupo');

            // Alumnos irregulares
            $irregulares = DB::table('alumno as a')
                ->join('inscripcion as i', 'a.id_alumno', '=', 'i.id_alumno')
                ->join('grupo as g', 'i.id_grupo', '=', 'g.id_grupo')
                ->join('calificacion as c', 'i.id_inscripcion', '=', 'c.id_inscripcion')
                ->where('a.id_carrera', $carrera->id_carrera)
                ->where('g.id_periodo', $periodoActual->id_periodo)
                ->where('c.calificacion', '<', 70)
                ->distinct('a.id_alumno')
                ->count('a.id_alumno');

            $resultado[] = [
                'id_carrera' => $carrera->id_carrera,
                'nombre' => $carrera->nombre,
                'total_grupos' => $totalGrupos,
                'total_alumnos' => $totalAlumnos,
                'alumnos_regulares' => $totalAlumnos - $irregulares,
                'alumnos_irregulares' => $irregulares,
            ];
        }

        return $resultado;
    }

    private function actividadReciente()
    {
        $actividad = collect();

        if (Schema::hasTable('bitacora')) {

            $actividad = $actividad->merge(

                DB::table('bitacora')
                    ->select(
                        DB::raw("COALESCE(accion, 'Actividad del sistema') as descripcion"),
                        'fecha_hora as fecha'
                    )
                    ->orderByDesc('fecha_hora')
                    ->limit(5)
                    ->get()
            );
        }

        $actividad = $actividad->merge(

            DB::table('inscripcion as i')
                ->leftJoin('alumno as a', 'i.id_alumno', '=', 'a.id_alumno')
                ->select(
                    DB::raw("
                        CONCAT(
                            'Inscripcion registrada para ',
                            COALESCE(a.numero_control, 'alumno sin control')
                        ) as descripcion
                    "),
                    'i.fecha_inscripcion as fecha'
                )
                ->whereNotNull('i.fecha_inscripcion')
                ->orderByDesc('i.fecha_inscripcion')
                ->limit(5)
                ->get()
        );

        $actividad = $actividad->merge(

            DB::table('calificacion as c')
                ->select(
                    DB::raw("'Calificacion registrada' as descripcion"),
                    'c.fecha_registro as fecha'
                )
                ->whereNotNull('c.fecha_registro')
                ->orderByDesc('c.fecha_registro')
                ->limit(5)
                ->get()
        );

        return $actividad
            ->filter(fn ($item) => !empty($item->fecha))
            ->sortByDesc('fecha')
            ->take(8)
            ->values();
    }
}
