<?php

namespace App\Http\Controllers\Seguridad;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Persona;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class UsuarioController extends Controller
{
    /**
     * Listar todos los usuarios con sus relaciones
     */
    public function index()
    {
        try {
            $usuarios = Usuario::with(['persona', 'roles'])
                ->select('id_usuario', 'nombre_usuario', 'estatus', 'id_persona')
                ->get();

            $resultado = $usuarios->map(function ($usuario) {
                $nombreCompleto = $usuario->persona
                    ? trim("{$usuario->persona->nombre} {$usuario->persona->apellido_paterno} {$usuario->persona->apellido_materno}")
                    : 'Sin nombre';

                return [
                    'id_usuario'      => $usuario->id_usuario,
                    'nombre_usuario'  => $usuario->nombre_usuario,
                    'estatus'         => $usuario->estatus ? 'Activo' : 'Inactivo',
                    'nombre_completo' => $nombreCompleto,
                    'roles'           => $usuario->roles
                        ? $usuario->roles->pluck('nombre_rol')->toArray()
                        : [],
                ];
            });

            return response()->json($resultado);
        } catch (\Exception $e) {
            Log::error("Error al listar usuarios: " . $e->getMessage());
            return response()->json(['error' => 'Error al cargar usuarios'], 500);
        }
    }

    /**
     * Buscar personas para asociar a un nuevo usuario
     * GET /api/personas/buscar?q=texto
     */
    public function buscarPersonas(Request $request)
    {
        try {
            $q = trim($request->get('q') ?? '');

            if (strlen($q) < 2) {
                return response()->json([]);
            }

            $personas = Persona::join('empleado as e', 'persona.id_persona', '=', 'e.id_persona')
                ->join('docente as d', 'e.id_empleado', '=', 'd.id_empleado')
                ->where(function ($query) use ($q) {
                    $query->where('persona.nombre', 'LIKE', "%{$q}%")
                        ->orWhere('persona.apellido_paterno', 'LIKE', "%{$q}%")
                        ->orWhere('persona.apellido_materno', 'LIKE', "%{$q}%")
                        ->orWhere(
                            DB::raw("CONCAT(persona.nombre, ' ', COALESCE(persona.apellido_paterno,''), ' ', COALESCE(persona.apellido_materno,''))"),
                            'LIKE', "%{$q}%"
                        );
                })
                ->select(
                    'persona.id_persona',
                    'persona.nombre',
                    'persona.apellido_paterno',
                    'persona.apellido_materno',
                    'persona.curp',
                    'd.id_docente'
                )
                ->limit(12)
                ->get();

            $resultado = $personas->map(function ($p) {
                return [
                    'id_persona'      => $p->id_persona,
                    'id_docente'      => $p->id_docente,
                    'nombre_completo' => trim("{$p->apellido_paterno} {$p->apellido_materno} {$p->nombre}"),
                    'curp'            => $p->curp,
                ];
            });

            return response()->json($resultado);

        } catch (\Exception $e) {
            Log::error('Error buscando personas: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }

    /**
     * Crear nuevo usuario
     * POST /api/usuarios
     *
     * Guarda la contraseña con bcrypt (Hash::make).
     * El AuthController ya soporta bcrypt como primera opcion,
     * por lo que los usuarios creados aqui pueden iniciar sesion normalmente.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'id_persona'     => 'required|exists:persona,id_persona',
                'nombre_usuario' => 'required|string|unique:usuario,nombre_usuario|max:50',
                'contrasena'     => 'required|string|min:4',
                'estatus'        => 'required|in:Activo,Inactivo',
                'roles'          => 'nullable|array',
            ], [
                'id_persona.required'     => 'Debes seleccionar una persona asociada.',
                'id_persona.exists'       => 'La persona seleccionada no existe en el sistema.',
                'nombre_usuario.required' => 'El nombre de usuario es obligatorio.',
                'nombre_usuario.unique'   => 'Ese nombre de usuario ya esta en uso.',
                'nombre_usuario.max'      => 'El nombre de usuario no puede superar 50 caracteres.',
                'contrasena.required'     => 'La contrasena es obligatoria.',
                'contrasena.min'          => 'La contrasena debe tener al menos 4 caracteres.',
                'estatus.in'             => 'El estatus debe ser Activo o Inactivo.',
            ]);

            $usuario = Usuario::create([
                'id_persona'     => $request->id_persona,
                'nombre_usuario' => $request->nombre_usuario,
                'password_hash'  => Hash::make($request->contrasena),
                'estatus'        => $request->estatus === 'Activo',
            ]);

            if (!empty($request->roles) && is_array($request->roles)) {
                $rolesIds = Rol::whereIn('nombre_rol', $request->roles)->pluck('id_rol');
                $usuario->roles()->sync($rolesIds);
            }

            DB::commit();

            return response()->json([
                'message' => 'Usuario creado correctamente.',
                'usuario' => $usuario->load('roles', 'persona'),
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'error'   => 'Datos invalidos.',
                'detalle' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error creando usuario: " . $e->getMessage());
            return response()->json(['error' => 'Error interno al crear el usuario.'], 500);
        }
    }

    /**
     * Actualizar usuario
     * PUT /api/usuarios/{id}
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $usuario = Usuario::findOrFail($id);

            $request->validate([
                'nombre_usuario' => "required|string|unique:usuario,nombre_usuario,{$id},id_usuario|max:50",
                'estatus'        => 'required|in:Activo,Inactivo',
                'roles'          => 'nullable|array',
            ], [
                'nombre_usuario.required' => 'El nombre de usuario es obligatorio.',
                'nombre_usuario.unique'   => 'Ese nombre de usuario ya esta en uso.',
                'estatus.in'             => 'El estatus debe ser Activo o Inactivo.',
            ]);

            $usuario->update([
                'nombre_usuario' => $request->nombre_usuario,
                'estatus'        => $request->estatus === 'Activo',
            ]);

            if ($request->has('roles')) {
                $rolesIds = Rol::whereIn('nombre_rol', $request->roles ?? [])->pluck('id_rol');
                $usuario->roles()->sync($rolesIds);
            }

            DB::commit();

            return response()->json([
                'message' => 'Usuario actualizado correctamente.',
                'data'    => $usuario->load('roles', 'persona'),
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'error'   => 'Datos invalidos.',
                'detalle' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error actualizando usuario {$id}: " . $e->getMessage());
            return response()->json(['error' => 'Error interno al actualizar el usuario.'], 500);
        }
    }

    /**
     * Cambiar contrasena
     * PUT /api/usuarios/{id}/contrasena
     */
    public function cambiarContrasena(Request $request, $id)
    {
        try {
            $request->validate([
                'contrasena' => 'required|string|min:4',
            ], [
                'contrasena.min' => 'La contrasena debe tener al menos 4 caracteres.',
            ]);

            $usuario = Usuario::findOrFail($id);
            $usuario->update([
                'password_hash' => Hash::make($request->contrasena),
            ]);

            return response()->json(['message' => 'Contrasena actualizada correctamente.']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error'   => 'Datos invalidos.',
                'detalle' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error("Error cambiando contrasena usuario {$id}: " . $e->getMessage());
            return response()->json(['error' => 'Error interno al cambiar la contrasena.'], 500);
        }
    }

    /**
     * Eliminar usuario
     * DELETE /api/usuarios/{id}
     */
    public function destroy($id)
    {
        try {
            $usuario = Usuario::findOrFail($id);
            $usuario->roles()->detach();
            $usuario->delete();

            return response()->json(['message' => 'Usuario eliminado correctamente.']);

        } catch (\Exception $e) {
            Log::error("Error eliminando usuario {$id}: " . $e->getMessage());
            return response()->json(['error' => 'Error interno al eliminar el usuario.'], 500);
        }
    }
}
