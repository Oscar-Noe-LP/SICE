<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class BitacoraService
{
    /**
     * Registra un evento en la bitácora con valores anteriores y nuevos en JSON
     *
     * @param string $accion     INSERT | UPDATE | DELETE
     * @param string $tabla      Nombre de la tabla afectada
     * @param mixed  $idRegistro ID del registro afectado
     * @param array  $anterior   Valores antes del cambio (vacío para INSERT)
     * @param array  $nuevo      Valores después del cambio (vacío para DELETE)
     * @param int|null $idUsuario ID del usuario que hizo el cambio
     */
    public static function registrar(
        string $accion,
        string $tabla,
        mixed  $idRegistro,
        array  $anterior = [],
        array  $nuevo    = [],
        ?int   $idUsuario = null
    ): void {
        try {
            // Buscar id_modulo desde la tabla modulo si existe
            $idModulo = DB::table('modulo')
                ->whereRaw('LOWER(nombre_modulo) LIKE ?', ['%' . strtolower($tabla) . '%'])
                ->value('id_modulo');

            $detalle = '';
            if (!empty($anterior)) $detalle .= ' | Antes: ' . json_encode($anterior, JSON_UNESCAPED_UNICODE);
            if (!empty($nuevo))    $detalle .= ' | Despues: ' . json_encode($nuevo, JSON_UNESCAPED_UNICODE);

            DB::table('bitacora')->insert([
                'accion'       => strtoupper($accion) . " en {$tabla} (ID: {$idRegistro})" . $detalle,
                'id_usuario'   => $idUsuario,
                'id_modulo'    => $idModulo,
                'direccion_ip' => Request::ip(),
                'fecha_hora'   => now(),
            ]);
        } catch (\Exception $e) {
            // La bitácora nunca debe interrumpir el flujo principal
            \Log::warning('BitacoraService error: ' . $e->getMessage());
        }
    }
}
