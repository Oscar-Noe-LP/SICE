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
     * Request:  { correo: string, contrasena: string }
     * Response: { success: true,  token: string, usuario: {...} }
     *        o: { success: false, message: string }
     *
     * Formato de correo institucional:
     *   nombre.iniciales@matehuala.tecnm.mx
     *   Ej: pedro.lj@matehuala.tecnm.mx
     *
     * Flujo:
     *   1. Busca el correo en persona_correo → obtiene id_persona
     *   2. Busca el usuario por id_persona   → obtiene usuario + hash
     *   3. Verifica contraseña (bcrypt → MD5 → texto plano como fallback)
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'correo'     => 'required|string|email',
                'contrasena' => 'required|string|min:1',
            ]);

            $correoBuscado = strtolower(trim($request->correo));
            $dominioValido = '@matehuala.tecnm.mx';

            // Validar dominio institucional
            if (!str_ends_with($correoBuscado, $dominioValido)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debes usar tu correo institucional (@matehuala.tecnm.mx).'
                ], 422);
            }

            // Buscar persona por correo institucional en persona_correo
            $personaCorreo = DB::table('persona_correo')
                ->where('correo', $correoBuscado)
                ->first();

            if (!$personaCorreo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Correo o contraseña incorrectos.'
                ], 401);
            }

            // Buscar usuario + datos de persona + rol usando el id_persona
            $usuario = DB::table('usuario as u')
                ->join('persona as p', 'u.id_persona', '=', 'p.id_persona')
                ->leftJoin('usuario_rol as ur', 'u.id_usuario', '=', 'ur.id_usuario')
                ->leftJoin('rol as r', 'ur.id_rol', '=', 'r.id_rol')
                ->where('u.id_persona', $personaCorreo->id_persona)
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

            // La persona existe en persona_correo pero no tiene usuario del sistema
            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta cuenta no tiene acceso al sistema. Contacta al administrador.'
                ], 403);
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
            // 3° texto plano como último recurso temporal
            try {
                $bcryptValido = Hash::check($request->contrasena, $usuario->password_hash);
            } catch (\Exception $e) {
                $bcryptValido = false;
            }

            $passwordValido = $bcryptValido
                        || md5($request->contrasena) === $usuario->password_hash
                        || $request->contrasena      === $usuario->password_hash;

            if (!$passwordValido) {
                return response()->json([
                    'success' => false,
                    'message' => 'Correo o contraseña incorrectos.'
                ], 401);
            }

            // Registrar último acceso
            DB::table('usuario')
                ->where('id_usuario', $usuario->id_usuario)
                ->update(['ultimo_acceso' => now()]);

            // Token simple sin Sanctum: base64 del id + correo + timestamp + random
            $token = base64_encode(
                $usuario->id_usuario . '|' .
                $correoBuscado       . '|' .
                time()               . '|' .
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
                    'correo'         => $correoBuscado,
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
     * El token vive en localStorage del frontend — basta con que Vue lo elimine.
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
