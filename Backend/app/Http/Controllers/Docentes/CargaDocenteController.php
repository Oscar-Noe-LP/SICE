<?php

namespace App\Http\Controllers\Docentes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CargaDocenteController extends Controller
{
    /**
     * GET /api/docentes?q=...
     *
     * Busca docentes por nombre o número de empleado.
     * La view espera: { id_docente, nombre, numero_empleado, especialidad, grado_academico }
     */
    public function buscarDocentes(Request $request)
    {
        try {
            $query = DB::table('docente as d')
                ->join('empleado as e', 'd.id_empleado', '=', 'e.id_empleado')
                ->join('persona as p', 'e.id_persona', '=', 'p.id_persona')
                ->select(
                    'd.id_docente',
                    DB::raw("TRIM(CONCAT(p.nombre, ' ',
                        COALESCE(p.apellido_paterno, ''), ' ',
                        COALESCE(p.apellido_materno, ''))) as nombre"),
                    'e.numero_empleado',
                    'd.especialidad',
                    'd.grado_academico'
                )
                ->where('e.estatus', true);

            // Filtro por búsqueda si se recibe el parámetro ?q=
            if ($request->filled('q')) {
                $termino = '%' . $request->q . '%';
                $query->where(function ($q) use ($termino) {
                    $q->where(DB::raw("CONCAT(p.nombre, ' ', COALESCE(p.apellido_paterno,''), ' ', COALESCE(p.apellido_materno,''))"), 'LIKE', $termino)
                      ->orWhere('e.numero_empleado', 'LIKE', $termino);
                });
            }

            // Limitar resultados solo cuando se busca (dropdown), no en listado completo
            if ($request->filled('q')) {
                $query->limit(20);
            }

            $docentes = $query
                ->orderBy('p.apellido_paterno')
                ->get();

            return response()->json($docentes);

        } catch (\Exception $e) {
            Log::error('Error en buscarDocentes(): ' . $e->getMessage());
            return response()->json(['error' => 'Error al buscar docentes', 'detalle' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/carga-docente/{id_docente}
     *
     * Devuelve los grupos asignados a un docente en el periodo activo.
     * La view espera por registro:
     * { id_asignacion, id_grupo, clave_grupo, materia, carrera, semestre,
     *   dia, hora_inicio, hora_fin, capacidad, inscritos, periodo }
     *
     * NOTA: La tabla `grupo` en el esquema actual no tiene columnas de horario
     * (dia, hora_inicio, hora_fin). Si las agregas en el futuro, solo descomenta
     * las líneas marcadas con [HORARIO]. Por ahora se devuelven como null.
     */
    public function cargaPorDocente($id_docente)
    {
        try {
            // Verificar que el docente exista
            $docenteExiste = DB::table('docente')->where('id_docente', $id_docente)->exists();
            if (!$docenteExiste) {
                return response()->json(['error' => 'Docente no encontrado'], 404);
            }

            $grupos = DB::table('grupo as g')
                ->join('materia as m', 'g.id_materia', '=', 'm.id_materia')
                ->join('periodo as per', 'g.id_periodo', '=', 'per.id_periodo')
                // Carrera: materia → plan_materia → plan_estudio → carrera
                ->leftJoin('plan_materia as pm', 'm.id_materia', '=', 'pm.id_materia')
                ->leftJoin('plan_estudio as pe', 'pm.id_plan', '=', 'pe.id_plan')
                ->leftJoin('carrera as car', 'pe.id_carrera', '=', 'car.id_carrera')
                // Aula (opcional, para referencia futura)
                ->leftJoin('aula as a', 'g.id_aula', '=', 'a.id_aula')
                ->select(
                    // id_asignacion: la view usa este campo en el DELETE.
                    // Como la asignación se guarda en grupo.id_docente (no hay tabla
                    // asignacion_docente separada), devolvemos id_grupo en ambos campos.
                    'g.id_grupo as id_asignacion',
                    'g.id_grupo',
                    'g.clave_grupo',
                    'm.nombre as materia',
                    DB::raw("COALESCE(car.nombre, 'Sin carrera') as carrera"),
                    DB::raw('COALESCE(pm.semestre, 0) as semestre'),

                    'g.dia',
                    'g.hora_inicio',
                    'g.hora_fin',

                    'g.capacidad',
                    DB::raw('(SELECT COUNT(*) FROM inscripcion i WHERE i.id_grupo = g.id_grupo) as inscritos'),
                    'per.nombre_periodo as periodo'
                )
                ->where('g.id_docente', $id_docente)
                ->where('per.estatus', true) // Solo periodo activo
                ->where('g.estatus', true)
                ->orderBy('g.clave_grupo')
                ->get();

            return response()->json($grupos);

        } catch (\Exception $e) {
            Log::error('Error en cargaPorDocente(): ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar la carga académica', 'detalle' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/asignacion-docente/{id}
     *
     * Desasigna el docente de un grupo poniendo id_docente = NULL.
     * El parámetro {id} es el id_grupo (ver nota en cargaPorDocente).
     *
     * IMPORTANTE: Este endpoint ya existe en AsignacionDocenteController si
     * tienes una ruta DELETE registrada. Si no, este método lo cubre.
     */
    public function desasignar($id)
    {
        try {
            $grupo = DB::table('grupo')->where('id_grupo', $id)->first();

            if (!$grupo) {
                return response()->json(['error' => 'Grupo no encontrado'], 404);
            }

            if (is_null($grupo->id_docente)) {
                return response()->json(['error' => 'El grupo ya no tiene docente asignado'], 422);
            }

            DB::table('grupo')
                ->where('id_grupo', $id)
                ->update(['id_docente' => null]);

            Log::info("Docente desasignado del grupo {$id}");

            return response()->json(['message' => 'Docente desasignado correctamente', 'id_grupo' => $id]);

        } catch (\Exception $e) {
            Log::error('Error en desasignar(): ' . $e->getMessage());
            return response()->json(['error' => 'No se pudo desasignar el docente', 'detalle' => $e->getMessage()], 500);
        }
    }
}
