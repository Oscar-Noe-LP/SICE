<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlanCurricularController extends Controller
{
    private function estatusTexto($estatus): string
    {
        return match ((int) $estatus) {
            1       => 'Activo',
            2       => 'En Revisión',
            default => 'Inactivo',
        };
    }

    private function mapPlan(object $p): array
    {
        return [
            'id'               => $p->id_plan,
            'nombre'           => $p->nombre_plan,
            'carrera'          => $p->nombre_carrera ?? null,
            'carrera_id'       => $p->id_carrera,
            'vigencia_inicio'  => $p->anio_vigencia . '-08-01',
            'vigencia_fin'     => ($p->anio_vigencia + 4) . '-07-31',
            'creditos_totales' => (int) $p->total_creditos,
            'estatus'          => $this->estatusTexto($p->estatus),
        ];
    }

    // 1. KPIs
    public function kpis()
    {
        try {
            $total      = DB::table('plan_estudio')->count();
            $activos    = DB::table('plan_estudio')->where('estatus', 1)->count();
            $enRevision = DB::table('plan_estudio')->where('estatus', 2)->count();

            return response()->json([
                'total'      => $total,
                'activos'    => $activos,
                'en_revision'=> $enRevision,
                'inactivos'  => max(0, $total - $activos - $enRevision),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 2. Lista con filtros
    public function index(Request $request)
    {
        try {
            $anioActual = (int) date('Y');

            $query = DB::table('plan_estudio as pe')
                ->leftJoin('carrera as c', 'pe.id_carrera', '=', 'c.id_carrera')
                ->select('pe.*', 'c.nombre as nombre_carrera');

            if ($request->filled('carrera')) {
                $query->where('pe.id_carrera', $request->carrera);
            }

            if ($request->filled('vigencia')) {
                match ($request->vigencia) {
                    'vigente' => $query->whereBetween('pe.anio_vigencia', [$anioActual - 4, $anioActual]),
                    'proximo' => $query->where('pe.anio_vigencia', '>', $anioActual),
                    'vencido' => $query->where('pe.anio_vigencia', '<', $anioActual - 4),
                    default   => null,
                };
            }

            $planes = $query->get()->map(fn($p) => $this->mapPlan($p));

            return response()->json(['planes' => $planes]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 3. Crear plan
    public function store(Request $request)
    {
        $request->validate([
            'carrera_id'      => 'required|integer',
            'nombre'          => 'required|string|max:200',
            'vigencia_inicio' => 'nullable|string',
            'creditos_totales'=> 'required|integer|min:1',
            'estatus'         => 'nullable|string',
        ]);

        try {
            $anio   = $request->vigencia_inicio
                ? (int) substr($request->vigencia_inicio, 0, 4)
                : (int) date('Y');

            $estatus = match (strtolower($request->estatus ?? 'activo')) {
                'activo'      => 1,
                'en revisión',
                'en revision' => 2,
                default       => 0,
            };

            $id = DB::table('plan_estudio')->insertGetId([
                'id_carrera'     => $request->carrera_id,
                'nombre_plan'    => $request->nombre,
                'anio_vigencia'  => $anio,
                'total_creditos' => $request->creditos_totales,
                'estatus'        => $estatus,
            ]);

            return response()->json(['id' => $id, 'message' => 'Plan creado exitosamente'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // 4. Actualizar plan
    public function update(Request $request, $id)
    {
        $request->validate([
            'carrera_id'      => 'sometimes|integer',
            'nombre'          => 'sometimes|string|max:200',
            'vigencia_inicio' => 'nullable|string',
            'creditos_totales'=> 'sometimes|integer|min:1',
            'estatus'         => 'nullable|string',
        ]);

        try {
            $plan = DB::table('plan_estudio')->where('id_plan', $id)->first();
            if (!$plan) return response()->json(['message' => 'Plan no encontrado'], 404);

            $data = [];
            if ($request->filled('carrera_id'))       $data['id_carrera']     = $request->carrera_id;
            if ($request->filled('nombre'))            $data['nombre_plan']    = $request->nombre;
            if ($request->filled('creditos_totales'))  $data['total_creditos'] = $request->creditos_totales;
            if ($request->filled('vigencia_inicio'))   $data['anio_vigencia']  = (int) substr($request->vigencia_inicio, 0, 4);
            if ($request->filled('estatus')) {
                $data['estatus'] = match (strtolower($request->estatus)) {
                    'activo'      => 1,
                    'en revisión',
                    'en revision' => 2,
                    default       => 0,
                };
            }

            DB::table('plan_estudio')->where('id_plan', $id)->update($data);

            return response()->json(['message' => 'Plan actualizado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // 5. Materias de un plan
    public function getMaterias($id)
    {
        try {
            $plan = DB::table('plan_estudio')->where('id_plan', $id)->first();
            if (!$plan) return response()->json(['message' => 'Plan no encontrado'], 404);

            $materias = DB::table('plan_materia as pm')
                ->join('materia as m', 'pm.id_materia', '=', 'm.id_materia')
                ->where('pm.id_plan', $id)
                ->select('m.id_materia', 'm.nombre', 'm.creditos', 'pm.semestre',
                    DB::raw("'Obligatoria' as tipo"))
                ->orderBy('pm.semestre')
                ->get();

            $prereqsPorMateria = DB::table('prerrequisito as pr')
                ->join('materia as m', 'pr.id_materia_prerrequisito', '=', 'm.id_materia')
                ->whereIn('pr.id_materia', $materias->pluck('id_materia'))
                ->select('pr.id_materia', 'm.id_materia as prereq_id', 'm.nombre as prereq_nombre')
                ->get()
                ->groupBy('id_materia');

            $resultado = $materias->map(fn($m) => [
                'id'            => $m->id_materia,
                'nombre'        => $m->nombre,
                'semestre'      => $m->semestre,
                'creditos'      => (int) $m->creditos,
                'tipo'          => $m->tipo,
                'prerequisitos' => ($prereqsPorMateria[$m->id_materia] ?? collect())
                    ->map(fn($p) => ['id' => $p->prereq_id, 'nombre' => $p->prereq_nombre])
                    ->values(),
            ]);

            return response()->json(['materias' => $resultado]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 6. Agregar materia a un plan
    public function addMateria(Request $request, $id)
    {
        $request->validate([
            'id_materia' => 'required_without:nombre|integer',
            'nombre'     => 'required_without:id_materia|string',
            'semestre'   => 'required|integer|min:1|max:12',
            'creditos'   => 'nullable|integer',
            'tipo'       => 'nullable|string',
        ]);

        try {
            $plan = DB::table('plan_estudio')->where('id_plan', $id)->first();
            if (!$plan) return response()->json(['message' => 'Plan no encontrado'], 404);

            $idMateria = $request->id_materia;

            if (!$idMateria && $request->filled('nombre')) {
                $materia = DB::table('materia')->where('nombre', $request->nombre)->first();
                if ($materia) {
                    $idMateria = $materia->id_materia;
                } else {
                    $idMateria = DB::table('materia')->insertGetId([
                        'nombre'   => $request->nombre,
                        'creditos' => $request->creditos ?? 0,
                        'estatus'  => 1,
                    ]);
                }
            }

            $existe = DB::table('plan_materia')
                ->where('id_plan', $id)
                ->where('id_materia', $idMateria)
                ->exists();

            if ($existe) {
                return response()->json(['message' => 'La materia ya está en el plan'], 409);
            }

            DB::table('plan_materia')->insert([
                'id_plan'    => $id,
                'id_materia' => $idMateria,
                'semestre'   => $request->semestre,
            ]);

            return response()->json(['message' => 'Materia agregada al plan'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // 7. Eliminar materia de un plan
    public function removeMateria($idPlan, $idMateria)
    {
        try {
            $deleted = DB::table('plan_materia')
                ->where('id_plan', $idPlan)
                ->where('id_materia', $idMateria)
                ->delete();

            if (!$deleted) return response()->json(['message' => 'Relación no encontrada'], 404);

            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // 8. Prerrequisitos de un plan
    public function getPrerequisitos($id)
    {
        try {
            $materiasDelPlan = DB::table('plan_materia')->where('id_plan', $id)->pluck('id_materia');

            $prereqs = DB::table('prerrequisito as pr')
                ->join('materia as mo', 'pr.id_materia', '=', 'mo.id_materia')
                ->join('materia as mp', 'pr.id_materia_prerrequisito', '=', 'mp.id_materia')
                ->whereIn('pr.id_materia', $materiasDelPlan)
                ->select(
                    DB::raw('ROW_NUMBER() OVER (ORDER BY pr.id_materia) as id'),
                    'pr.id_materia as materia_origen_id',
                    'mo.nombre as materia_origen_nombre',
                    'pr.id_materia_prerrequisito as materia_prerequisito_id',
                    'mp.nombre as materia_prerequisito_nombre'
                )
                ->get();

            return response()->json(['prerequisitos' => $prereqs]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 9. Agregar prerrequisito
    public function addPrerequisito(Request $request, $id)
    {
        $request->validate([
            'materia_origen_id'      => 'required|integer',
            'materia_prerequisito_id'=> 'required|integer',
        ]);

        try {
            $existe = DB::table('prerrequisito')
                ->where('id_materia', $request->materia_origen_id)
                ->where('id_materia_prerrequisito', $request->materia_prerequisito_id)
                ->exists();

            if ($existe) return response()->json(['message' => 'El prerrequisito ya existe'], 409);

            DB::table('prerrequisito')->insert([
                'id_materia'              => $request->materia_origen_id,
                'id_materia_prerrequisito'=> $request->materia_prerequisito_id,
            ]);

            return response()->json(['message' => 'Prerrequisito agregado'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // 10. Eliminar prerrequisito
    public function removePrerequisito($idPlan, $idPrerequisito)
    {
        try {
            $deleted = DB::table('prerrequisito')
                ->where('id_materia', $idPrerequisito)
                ->delete();

            if (!$deleted) return response()->json(['message' => 'Prerrequisito no encontrado'], 404);

            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
