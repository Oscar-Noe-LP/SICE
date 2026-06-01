<?php

namespace App\Http\Controllers\Seguridad; // 🔥 ESTE ES EL CAMBIO

use App\Http\Controllers\Controller;
use App\Models\Permiso;
use Illuminate\Http\Request;

class PermisoController extends Controller
{
    public function index()
    {
        try {
            $permisos = Permiso::with('modulos')->get();

            $data = $permisos->map(function ($permiso) {
                return [
                    'id_permiso' => $permiso->id_permiso,
                    'nombre' => $permiso->nombre_permiso,
                    'descripcion' => $permiso->descripcion,
                    'modulo' => $permiso->modulos->first()->nombre_modulo ?? null,
                    'clave' => strtolower(str_replace(' ', '.', $permiso->nombre_permiso))
                ];
            });

            return response()->json($data);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al cargar permisos',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }
}