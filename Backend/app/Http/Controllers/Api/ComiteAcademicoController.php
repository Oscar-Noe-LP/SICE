<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ComiteAcademicoController extends Controller
{
    public function dashboard()
    {
        try {
            $pendientes = DB::table('solicitud_comite')
                ->where('estatus', 'Pendiente')
                ->count();

            $resueltas = DB::table('resolucion_comite')->count();

            $sesiones = DB::table('sesion_comite')
                ->whereDate('fecha', '>=', now()->toDateString())
                ->count();

            $solicitudesPendientes = DB::table('solicitud_comite as s')
                ->join('tipo_solicitud as t', 't.id_tipo_solicitud', '=', 's.id_tipo_solicitud')
                ->join('persona as p', 'p.id_persona', '=', 's.id_persona')
                ->select(
                    's.id_solicitud as id',
                    DB::raw("CONCAT(
                        p.nombre, ' ',
                        COALESCE(p.apellido_paterno, ''), ' ',
                        COALESCE(p.apellido_materno, '')
                    ) as folio"),
                    DB::raw("TRIM(CONCAT(
                        p.nombre, ' ',
                        COALESCE(p.apellido_paterno, ''), ' ',
                        COALESCE(p.apellido_materno, '')
                    )) as solicitante"),
                    't.nombre_tipo as tipo',
                    DB::raw('DATE(s.fecha_solicitud) as fecha'),
                    's.estatus'
                )
                ->where('s.estatus', 'Pendiente')
                ->orderByDesc('s.fecha_solicitud')
                ->limit(5)
                ->get()
                ->map(function ($item) {
                    $item->folio = 'SOL-' . str_pad($item->id, 4, '0', STR_PAD_LEFT);
                    return $item;
                });

            $sesionesRecientes = DB::table('sesion_comite as sc')
                ->leftJoin('resolucion_comite as rc', 'rc.id_sesion', '=', 'sc.id_sesion')
                ->select(
                    'sc.id_sesion as id',
                    DB::raw('DATE(sc.fecha) as fecha'),
                    'sc.descripcion',
                    DB::raw('COUNT(rc.id_resolucion) as resoluciones')
                )
                ->groupBy('sc.id_sesion', 'sc.fecha', 'sc.descripcion')
                ->orderBy('sc.fecha', 'asc')
                ->limit(3)
                ->get();

            return response()->json([
                'kpis' => [
                    'pendientes' => $pendientes,
                    'resueltas' => $resueltas,
                    'sesiones' => $sesiones,
                ],
                'solicitudes_pendientes' => $solicitudesPendientes,
                'sesiones_recientes' => $sesionesRecientes,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al cargar dashboard del comité.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function tiposSolicitud()
    {
        try {
            $tipos = DB::table('tipo_solicitud')
                ->select(
                    'id_tipo_solicitud as id',
                    'nombre_tipo as nombre'
                )
                ->orderBy('nombre_tipo')
                ->get();
            return response()->json($tipos);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al cargar tipos de solicitud.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function indexSolicitudes(Request $request)
    {
        try {
            $query = DB::table('solicitud_comite as s')
                ->join('tipo_solicitud as t', 't.id_tipo_solicitud', '=', 's.id_tipo_solicitud')
                ->join('persona as p', 'p.id_persona', '=', 's.id_persona')
                ->leftJoin('resolucion_comite as rc', 'rc.id_solicitud', '=', 's.id_solicitud')
                ->select(
                    's.id_solicitud as id',
                    DB::raw("CONCAT('SOL-', LPAD(s.id_solicitud, 4, '0')) as folio"),
                    DB::raw("TRIM(CONCAT(
                        p.nombre, ' ',
                        COALESCE(p.apellido_paterno, ''), ' ',
                        COALESCE(p.apellido_materno, '')
                    )) as solicitante"),
                    't.nombre_tipo as tipo',
                    DB::raw('DATE(s.fecha_solicitud) as fecha'),
                    's.estatus',
                    'rc.id_resolucion as resolucion_id'
                );

            if ($request->filled('tipo')) {
                $query->where('t.nombre_tipo', $request->tipo);
            }

            if ($request->filled('estatus')) {
                $query->where('s.estatus', $request->estatus);
            }

            if ($request->sin_resolucion == 1 || $request->sin_resolucion == '1') {
                $query->whereNull('rc.id_resolucion');
            }

            $solicitudes = $query
                ->orderByDesc('s.fecha_solicitud')
                ->get();

            return response()->json($solicitudes);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al cargar solicitudes.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function storeSolicitud(Request $request)
    {
        // 🔥 AQUÍ VA (justo al inicio del método)
        $id_tipo_solicitud = $request->id_tipo_solicitud ?? $request->tipo_solicitud_id;
        $id_persona = $request->id_persona ?? $request->persona_id;

        // 🔹 VALIDACIÓN (después de obtener valores)
        $validator = Validator::make([
            'id_tipo_solicitud' => $id_tipo_solicitud,
            'id_persona' => $id_persona,
            'descripcion' => $request->descripcion
        ], [
            'id_tipo_solicitud' => 'required|integer|exists:tipo_solicitud,id_tipo_solicitud',
            'id_persona' => 'required|integer|exists:persona,id_persona',
            'descripcion' => 'required|string|min:10'
        ], [
            'id_tipo_solicitud.required' => 'El tipo de solicitud es obligatorio.',
            'id_persona.required' => 'La persona solicitante es obligatoria.',
            'descripcion.required' => 'La descripción es obligatoria.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos inválidos.',
                'errors' => $validator->errors()
            ], 422);
        }

        // 🔹 INSERT (usa las variables ya adaptadas)
        try {
            $id = DB::table('solicitud_comite')->insertGetId([
                'id_persona' => $id_persona,
                'id_tipo_solicitud' => $id_tipo_solicitud,
                'descripcion' => trim($request->descripcion),
                'fecha_solicitud' => now(),
                'estatus' => 'Pendiente'
            ]);

            return response()->json([
                'message' => 'Solicitud registrada correctamente.',
                'data' => [
                    'id' => $id,
                    'folio' => 'SOL-' . str_pad($id, 4, '0', STR_PAD_LEFT),
                    'estatus' => 'Pendiente'
                ]
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al registrar la solicitud.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function indexSesiones()
    {
        try {
            $sesiones = DB::table('sesion_comite as sc')
                ->leftJoin('resolucion_comite as rc', 'rc.id_sesion', '=', 'sc.id_sesion')
                ->select(
                    'sc.id_sesion as id',
                    DB::raw('DATE(sc.fecha) as fecha'),
                    'sc.descripcion',
                    DB::raw('COUNT(rc.id_resolucion) as resoluciones')
                )
                ->groupBy('sc.id_sesion', 'sc.fecha', 'sc.descripcion')
                ->orderByDesc('sc.fecha')
                ->get();

            return response()->json($sesiones);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al cargar sesiones.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function storeSesion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fecha' => 'required|date',
            'descripcion' => 'required|string|min:5'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos inválidos.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $id = DB::table('sesion_comite')->insertGetId([
                'fecha' => $request->fecha,
                'descripcion' => trim($request->descripcion)
            ]);

            return response()->json([
                'message' => 'Sesión registrada correctamente.',
                'data' => [
                    'id' => $id,
                    'fecha' => $request->fecha,
                    'descripcion' => trim($request->descripcion),
                    'resoluciones' => 0
                ]
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al registrar la sesión.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateSesion(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'fecha' => 'required|date',
            'descripcion' => 'required|string|min:5'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos inválidos.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $existe = DB::table('sesion_comite')
                ->where('id_sesion', $id)
                ->exists();

            if (!$existe) {
                return response()->json([
                    'message' => 'Sesión no encontrada.'
                ], 404);
            }

            DB::table('sesion_comite')
                ->where('id_sesion', $id)
                ->update([
                    'fecha' => $request->fecha,
                    'descripcion' => trim($request->descripcion)
                ]);

            $sesion = DB::table('sesion_comite as sc')
                ->leftJoin('resolucion_comite as rc', 'rc.id_sesion', '=', 'sc.id_sesion')
                ->select(
                    'sc.id_sesion as id',
                    DB::raw('DATE(sc.fecha) as fecha'),
                    'sc.descripcion',
                    DB::raw('COUNT(rc.id_resolucion) as resoluciones')
                )
                ->where('sc.id_sesion', $id)
                ->groupBy('sc.id_sesion', 'sc.fecha', 'sc.descripcion')
                ->first();

            return response()->json([
                'message' => 'Sesión actualizada correctamente.',
                'data' => $sesion
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al actualizar la sesión.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function indexResoluciones(Request $request)
    {
        try {
            $query = DB::table('resolucion_comite as rc')
                ->join('solicitud_comite as s', 's.id_solicitud', '=', 'rc.id_solicitud')
                ->join('tipo_solicitud as t', 't.id_tipo_solicitud', '=', 's.id_tipo_solicitud')
                ->join('persona as p', 'p.id_persona', '=', 's.id_persona')
                ->join('sesion_comite as sc', 'sc.id_sesion', '=', 'rc.id_sesion')
                ->select(
                    'rc.id_resolucion as id',
                    DB::raw("CONCAT('SOL-', LPAD(s.id_solicitud, 4, '0')) as folio_solicitud"),
                    DB::raw("TRIM(CONCAT(
                        p.nombre, ' ',
                        COALESCE(p.apellido_paterno, ''), ' ',
                        COALESCE(p.apellido_materno, '')
                    )) as solicitante"),
                    't.nombre_tipo as tipo',
                    'sc.id_sesion as sesion_id',
                    DB::raw('DATE(sc.fecha) as fecha_sesion'),
                    'rc.decision',
                    DB::raw('DATE(rc.fecha_resolucion) as fecha')
                );

            if ($request->filled('sesion_id')) {
                $query->where('sc.id_sesion', $request->sesion_id);
            }

            if ($request->filled('tipo')) {
                $query->where('t.nombre_tipo', $request->tipo);
            }

            $resoluciones = $query
                ->orderByDesc('rc.fecha_resolucion')
                ->get();

            return response()->json($resoluciones);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al cargar resoluciones.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function buscarPersonas(Request $request)
    {
        try {
            $q = trim($request->q ?? '');

            if ($q === '') {
                return response()->json([]);
            }

            $personas = DB::table('persona')
                ->select(
                    'id_persona as id',
                    'nombre',
                    'apellido_paterno',
                    'apellido_materno'
                )
                ->where(function ($query) use ($q) {
                    $query->where('nombre', 'like', "%{$q}%")
                        ->orWhere('apellido_paterno', 'like', "%{$q}%")
                        ->orWhere('apellido_materno', 'like', "%{$q}%")
                        ->orWhere('id_persona', 'like', "%{$q}%");
                })
                ->limit(10)
                ->get();

            return response()->json($personas);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al buscar personas.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function storeResolucion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_sesion' => 'required|integer|exists:sesion_comite,id_sesion',
            'id_solicitud' => 'required|integer|exists:solicitud_comite,id_solicitud',
            'decision' => 'required|string|min:5'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos inválidos.',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $yaExiste = DB::table('resolucion_comite')
                ->where('id_solicitud', $request->id_solicitud)
                ->exists();

            if ($yaExiste) {
                return response()->json([
                    'message' => 'La solicitud ya tiene una resolución registrada.'
                ], 409);
            }

            $id = DB::table('resolucion_comite')->insertGetId([
                'id_sesion' => $request->id_sesion,
                'id_solicitud' => $request->id_solicitud,
                'decision' => trim($request->decision),
                'fecha_resolucion' => now()->toDateString()
            ]);

            $decisionTexto = mb_strtolower(trim($request->decision));
            $nuevoEstatus = 'Resuelta';

            if (str_contains($decisionTexto, 'aprueba') || str_contains($decisionTexto, 'aprobada')) {
                $nuevoEstatus = 'Aprobada';
            } elseif (str_contains($decisionTexto, 'rechaza') || str_contains($decisionTexto, 'rechazada')) {
                $nuevoEstatus = 'Rechazada';
            }

            DB::table('solicitud_comite')
                ->where('id_solicitud', $request->id_solicitud)
                ->update([
                    'estatus' => $nuevoEstatus
                ]);

            DB::commit();

            return response()->json([
                'message' => 'Resolución registrada correctamente.',
                'data' => [
                    'id' => $id
                ]
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al registrar la resolución.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}