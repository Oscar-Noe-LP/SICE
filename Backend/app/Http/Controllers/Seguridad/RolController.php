<?php

namespace App\Http\Controllers\Seguridad;

use App\Models\Rol;
use App\Models\Permiso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class RolController extends Controller
{
    public function index()
    {
        try {
            $roles = Rol::select(
                'id_rol',
                'nombre_rol as nombre',
                'descripcion',
                DB::raw("CASE WHEN estatus = 1 THEN 'Activo' ELSE 'Inactivo' END as estatus")
            )->get();

            return response()->json($roles);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'Error al cargar roles'], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|max:100|unique:rol,nombre_rol',
            'estatus' => 'required|in:Activo,Inactivo'
        ]);

        $rol = Rol::create([
            'nombre_rol' => $request->nombre,
            'descripcion' => $request->descripcion,
            'estatus' => $request->estatus === 'Activo' ? 1 : 0
        ]);

        return response()->json($rol, 201);
    }

    public function update(Request $request, $id)
    {
        $rol = Rol::findOrFail($id);

        $rol->update([
            'nombre_rol' => $request->nombre,
            'descripcion' => $request->descripcion,
            'estatus' => $request->estatus === 'Activo' ? 1 : 0
        ]);

        return response()->json($rol);
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            DB::table('rol_permiso')->where('id_rol', $id)->delete();

            Rol::findOrFail($id)->delete();

            DB::commit();

            return response()->json(['message' => 'Eliminado']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json(['error' => 'Error al eliminar'], 500);
        }
    }

    /**
     * Obtener los permisos de un rol (regresa IDs)
     */
    public function permisos($id)
    {
        $rol = Rol::with('permisos')->findOrFail($id);

        return response()->json([
            'permisos' => $rol->permisos->pluck('id_permiso')
        ]);
    }

    /**
     * Actualizar los permisos de un rol usando IDs directamente
     */
    public function actualizarPermisos(Request $request, $id)
    {
        $request->validate([
            'permisos' => 'array|nullable'
        ]);

        try {
            $rol = Rol::findOrFail($id);

            // Sync directo con los IDs que envía el frontend
            $rol->permisos()->sync($request->permisos ?? []);

            return response()->json(['mensaje' => 'Permisos actualizados correctamente']);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'Error al guardar los permisos'], 500);
        }
    }


    public function simpleList()
    {
        try {
            $roles = Rol::select('id_rol', 'nombre_rol as nombre', 'descripcion')
                        ->where('estatus', true)   // solo roles activos
                        ->orderBy('nombre_rol')
                        ->get();

            return response()->json($roles);
        } catch (\Exception $e) {
            Log::error('Error en simpleList roles: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar roles'], 500);
        }
    }
}