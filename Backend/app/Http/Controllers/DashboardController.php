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
                'actividad_reciente' => $this->actividadReciente(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error en servidor',
                'mensaje' => $e->getMessage(),
            ], 500);
        }
    }

    private function alumnosConEstatus(array $estatus)
    {
        $normalizados = array_map(fn ($valor) => mb_strtolower($valor), $estatus);
        $query = DB::table('alumno as a');
        $tieneCatalogoEstatus = Schema::hasTable('estatus_alumno')
            && Schema::hasColumn('alumno', 'id_estatus_alumno');

        if ($tieneCatalogoEstatus) {
            $query->leftJoin('estatus_alumno as ea', 'a.id_estatus_alumno', '=', 'ea.id_estatus_alumno');
        }

        return $query->where(function ($q) use ($normalizados, $tieneCatalogoEstatus) {
            if (Schema::hasColumn('alumno', 'estatus')) {
                $q->whereIn(DB::raw('LOWER(CAST(a.estatus AS CHAR))'), $normalizados);
            }

            if ($tieneCatalogoEstatus) {
                $q->orWhereIn(DB::raw('LOWER(ea.nombre)'), $normalizados);
            }
        });
    }

    private function gruposActivos()
    {
        $query = DB::table('grupo');

        if (Schema::hasColumn('grupo', 'estatus')) {
            $query->whereIn(DB::raw('LOWER(CAST(estatus AS CHAR))'), ['activo', '1', 'true']);
        }

        return $query;
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
                    DB::raw("CONCAT('Inscripcion registrada para ', COALESCE(a.numero_control, 'alumno sin control')) as descripcion"),
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
