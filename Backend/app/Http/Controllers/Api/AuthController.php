<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * POST /api/login
     *
     * Request:  { nombre_usuario: string, contrasena: string }
     * Response: { success: true,  token: string, usuario: {...} }
     *        o: { success: false, message: string }
     *
     * Verifica contra la tabla `usuario` de la BD SICE.
     * Soporta bcrypt (Hash::make de Laravel) y MD5 como fallback
     * para contraseñas insertadas manualmente en la BD.
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'nombre_usuario' => 'required|string',
                'contrasena'     => 'required|string|min:1',
            ]);

            // Buscar usuario + datos de persona + rol en un solo query
            $usuario = DB::table('usuario as u')
                ->join('persona as p', 'u.id_persona', '=', 'p.id_persona')
                ->leftJoin('usuario_rol as ur', 'u.id_usuario', '=', 'ur.id_usuario')
                ->leftJoin('rol as r', 'ur.id_rol', '=', 'r.id_rol')
                ->where('u.nombre_usuario', $request->nombre_usuario)
                ->select(
                    'u.id_usuario',
                    'u.nombre_usuario',
                    'u.password_hash',
                    'u.estatus',
                    'p.id_persona',
                    'p.nombre',
                    'p.apellido_paterno',
                    'p.apellido_materno',
                    'r.id_rol',
                    'r.nombre_rol'
                )
                ->first();

            // Usuario no existe
            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario o contraseña incorrectos.'
                ], 401);
            }

            // Cuenta desactivada
            if (!$usuario->estatus) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tu cuenta está desactivada. Contacta al administrador.'
                ], 403);
            }

            // Verificar contraseña:
            // 1° bcrypt via Hash::check (estándar Laravel)
            // 2° MD5 como fallback para passwords insertados manualmente en la BD
            // Después — bcrypt con fallback seguro a MD5
            try {
                $bcryptValido = Hash::check($request->contrasena, $usuario->password_hash);
            } catch (\Exception $e) {
                $bcryptValido = false;
            }

            $passwordValido = $bcryptValido
                        || md5($request->contrasena) === $usuario->password_hash
                        || $request->contrasena     === $usuario->password_hash; // texto plano temporal

            if (!$passwordValido) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario o contraseña incorrectos.'
                ], 401);
            }

            // Registrar último acceso
            DB::table('usuario')
                ->where('id_usuario', $usuario->id_usuario)
                ->update(['ultimo_acceso' => now()]);

            // Token simple sin Sanctum: base64 del id + usuario + timestamp + random
            // El frontend lo guarda en localStorage y lo manda como Bearer en cada request
            $token = base64_encode(
                $usuario->id_usuario . '|' .
                $usuario->nombre_usuario . '|' .
                time() . '|' .
                bin2hex(random_bytes(16))
            );

            $nombreCompleto = trim(
                ($usuario->nombre           ?? '') . ' ' .
                ($usuario->apellido_paterno ?? '') . ' ' .
                ($usuario->apellido_materno ?? '')
            );

            return response()->json([
                'success' => true,
                'token'   => $token,
                'usuario' => [
                    'id_usuario'     => $usuario->id_usuario,
                    'nombre_usuario' => $usuario->nombre_usuario,
                    'nombre'         => $nombreCompleto,
                    'id_rol'         => $usuario->id_rol,
                    'rol'            => $usuario->nombre_rol ?? 'Sin rol',
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos incompletos.',
                'detalle' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error en login: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor. Intenta de nuevo.'
            ], 500);
        }
    }

    /**
     * POST /api/logout
     * El token vive en localStorage del frontend — basta con que el Vue lo elimine.
     * Este endpoint solo registra el último acceso.
     */
    public function logout(Request $request)
    {
        try {
            $tokenDecoded = base64_decode($request->bearerToken() ?? '');
            $partes       = explode('|', $tokenDecoded);
            $idUsuario    = $partes[0] ?? null;

            if ($idUsuario && is_numeric($idUsuario)) {
                DB::table('usuario')
                    ->where('id_usuario', $idUsuario)
                    ->update(['ultimo_acceso' => now()]);
            }
        } catch (\Exception $e) {
            // Silencioso — el frontend ya borró el token
        }

        return response()->json(['success' => true, 'message' => 'Sesión cerrada.']);
    }
}
