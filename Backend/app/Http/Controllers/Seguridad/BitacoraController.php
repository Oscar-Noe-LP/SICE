<?php

namespace App\Http\Controllers\Seguridad;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class BitacoraController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Usar DB::table en lugar de Eloquent para evitar conflictos entre
            // with(['usuario','modulo']) y los leftJoin manuales (ambos alias
            // colisionaban con los nombres de relación, causando SQL 500).
            $query = DB::table('bitacora')
                ->leftJoin('usuario',  'bitacora.id_usuario', '=', 'usuario.id_usuario')
                ->leftJoin('persona',  'usuario.id_persona',  '=', 'persona.id_persona')
                ->leftJoin('modulo',   'bitacora.id_modulo',  '=', 'modulo.id_modulo')
                ->select(
                    'bitacora.id_bitacora',
                    'bitacora.id_usuario',
                    'bitacora.id_modulo',
                    'bitacora.fecha_hora',
                    'bitacora.accion',
                    'bitacora.direccion_ip',
                    DB::raw("COALESCE(CONCAT(persona.nombre, ' ', persona.apellido_paterno), 'Sistema') as nombre_usuario"),
                    'modulo.nombre_modulo'
                )
                ->orderBy('bitacora.fecha_hora', 'desc');

            // Filtros
            if ($request->filled('usuario')) {
                $query->whereRaw("CONCAT(persona.nombre, ' ', persona.apellido_paterno) LIKE ?", ['%' . $request->usuario . '%']);
            }

            if ($request->filled('modulo')) {
                $query->where('modulo.nombre_modulo', $request->modulo);
            }

            if ($request->filled('accion')) {
                $query->where('bitacora.accion', 'LIKE', '%' . $request->accion . '%');
            }

            if ($request->filled('fecha_desde')) {
                $query->whereDate('bitacora.fecha_hora', '>=', $request->fecha_desde);
            }

            if ($request->filled('fecha_hasta')) {
                $query->whereDate('bitacora.fecha_hora', '<=', $request->fecha_hasta);
            }

            if ($request->filled('limit')) {
                $query->limit((int) $request->limit);
            }

            $bitacora = $query->get();

            // Formato que espera el Vue
            $data = $bitacora->map(function ($item) {
                return [
                    'id_bitacora' => $item->id_bitacora,
                    'fecha_hora'  => $item->fecha_hora,
                    'usuario'     => $item->nombre_usuario ?? 'Sistema',
                    'accion'      => $item->accion,
                    'modulo'      => $item->nombre_modulo ?? 'Desconocido',
                    'descripcion' => $item->accion,
                    'ip'          => $item->direccion_ip,
                ];
            });

            return response()->json($data);

        } catch (\Exception $e) {
            \Log::error('Error en bitácora: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar la bitácora'], 500);
        }
    }
}