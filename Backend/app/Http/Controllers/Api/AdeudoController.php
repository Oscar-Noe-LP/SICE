<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdeudoController extends Controller
{
    /**
     * GET /api/adeudos?id_alumno=X
     * Lista adeudos de un alumno (o todos si no se filtra)
     */
    public function index(Request $request)
    {
        $query = DB::table('adeudo as ad')
            ->join('alumno as a', 'ad.id_alumno', '=', 'a.id_alumno')
            ->join('persona as p', 'a.id_persona', '=', 'p.id_persona')
            ->select(
                'ad.id_adeudo',
                'ad.id_alumno',
                'a.numero_control',
                DB::raw("CONCAT(p.nombre, ' ', p.apellido_paterno, ' ', p.apellido_materno) as nombre_alumno"),
                'ad.concepto',
                'ad.monto',
                'ad.pagado',
                'ad.fecha_limite',
                'ad.created_at'
            )
            ->orderBy('ad.pagado')
            ->orderBy('ad.created_at', 'desc');

        if ($request->filled('id_alumno')) {
            $query->where('ad.id_alumno', $request->id_alumno);
        }

        if ($request->filled('solo_pendientes') && $request->solo_pendientes) {
            $query->where('ad.pagado', false);
        }

        return response()->json($query->get());
    }

    /**
     * POST /api/adeudos
     * Registrar un nuevo adeudo
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_alumno'    => 'required|integer|exists:alumno,id_alumno',
            'concepto'     => 'required|string|max:150',
            'monto'        => 'required|numeric|min:0',
            'fecha_limite' => 'nullable|date',
        ]);

        $id = DB::table('adeudo')->insertGetId([
            'id_alumno'    => $request->id_alumno,
            'concepto'     => $request->concepto,
            'monto'        => $request->monto,
            'pagado'       => false,
            'fecha_limite' => $request->fecha_limite,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        BitacoraService::registrar('INSERT', 'adeudo', $id, [], [
            'id_alumno' => $request->id_alumno,
            'concepto'  => $request->concepto,
            'monto'     => $request->monto,
        ]);

        return response()->json(['mensaje' => 'Adeudo registrado', 'id_adeudo' => $id], 201);
    }

    /**
     * PUT /api/adeudos/{id}
     * Editar concepto, monto o fecha_limite de un adeudo
     */
    public function update(Request $request, int $id)
    {
        $adeudo = DB::table('adeudo')->where('id_adeudo', $id)->first();

        if (!$adeudo) {
            return response()->json(['error' => 'Adeudo no encontrado'], 404);
        }

        $request->validate([
            'concepto'     => 'sometimes|string|max:150',
            'monto'        => 'sometimes|numeric|min:0',
            'fecha_limite' => 'sometimes|nullable|date',
            'pagado'       => 'sometimes|boolean',
        ]);

        $campos = array_filter([
            'concepto'     => $request->concepto,
            'monto'        => $request->monto,
            'fecha_limite' => $request->fecha_limite,
            'pagado'       => $request->has('pagado') ? $request->pagado : null,
            'updated_at'   => now(),
        ], fn($v) => $v !== null);

        DB::table('adeudo')->where('id_adeudo', $id)->update($campos);

        BitacoraService::registrar('UPDATE', 'adeudo', $id, (array) $adeudo, $campos);

        return response()->json(['mensaje' => 'Adeudo actualizado', 'id_adeudo' => $id]);
    }

    /**
     * PUT /api/adeudos/{id}/marcar-pagado
     * Marcar un adeudo como pagado
     */
    public function marcarPagado(int $id)
    {
        $adeudo = DB::table('adeudo')->where('id_adeudo', $id)->first();

        if (!$adeudo) {
            return response()->json(['error' => 'Adeudo no encontrado'], 404);
        }

        DB::table('adeudo')->where('id_adeudo', $id)->update([
            'pagado'     => true,
            'updated_at' => now(),
        ]);

        BitacoraService::registrar('UPDATE', 'adeudo', $id,
            ['pagado' => false],
            ['pagado' => true]
        );

        return response()->json(['mensaje' => 'Adeudo marcado como pagado']);
    }

    /**
     * DELETE /api/adeudos/{id}
     * Eliminar un adeudo
     */
    public function destroy(int $id)
    {
        $adeudo = DB::table('adeudo')->where('id_adeudo', $id)->first();

        if (!$adeudo) {
            return response()->json(['error' => 'Adeudo no encontrado'], 404);
        }

        BitacoraService::registrar('DELETE', 'adeudo', $id, (array) $adeudo);
        DB::table('adeudo')->where('id_adeudo', $id)->delete();

        return response()->json(['mensaje' => 'Adeudo eliminado']);
    }
}
