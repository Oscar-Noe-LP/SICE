<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BitacoraService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MicrosoftAuthController extends Controller
{
    private function cfg(): array
    {
        return [
            'client_id'     => env('MICROSOFT_CLIENT_ID'),
            'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
            'tenant_id'     => env('MICROSOFT_TENANT_ID'),
            'redirect_uri'  => env('MICROSOFT_REDIRECT_URI'),
        ];
    }

    /**
     * GET /api/auth/microsoft
     * Redirige al login de Microsoft
     */
    public function redirect()
    {
        $cfg = $this->cfg();

        $params = http_build_query([
            'client_id'     => $cfg['client_id'],
            'response_type' => 'code',
            'redirect_uri'  => $cfg['redirect_uri'],
            'scope'         => 'openid email profile User.Read',
            'response_mode' => 'query',
            'prompt'        => 'select_account',
        ]);

        return redirect(
            "https://login.microsoftonline.com/{$cfg['tenant_id']}/oauth2/v2.0/authorize?{$params}"
        );
    }

    /**
     * GET /api/auth/microsoft/callback
     * Microsoft regresa aquí con el código de autorización
     */
    public function callback(\Illuminate\Http\Request $request)
    {
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');

        // Microsoft regresó un error (usuario canceló, etc.)
        if ($request->has('error')) {
            Log::warning('Microsoft OAuth error: ' . $request->error_description);
            return redirect("{$frontendUrl}/login?error=" . urlencode($request->error_description ?? 'Error de autenticación'));
        }

        $code = $request->get('code');
        if (!$code) {
            return redirect("{$frontendUrl}/login?error=codigo_invalido");
        }

        try {
            $cfg = $this->cfg();

            // 1. Intercambiar código por access_token
            $tokenResponse = Http::asForm()->post(
                "https://login.microsoftonline.com/{$cfg['tenant_id']}/oauth2/v2.0/token",
                [
                    'client_id'     => $cfg['client_id'],
                    'client_secret' => $cfg['client_secret'],
                    'code'          => $code,
                    'redirect_uri'  => $cfg['redirect_uri'],
                    'grant_type'    => 'authorization_code',
                    'scope'         => 'openid email profile User.Read',
                ]
            );

            if ($tokenResponse->failed()) {
                Log::error('Microsoft token exchange failed: ' . $tokenResponse->body());
                return redirect("{$frontendUrl}/login?error=token_fallido");
            }

            $accessToken = $tokenResponse->json('access_token');

            // 2. Obtener datos del usuario desde Microsoft Graph
            $graphResponse = Http::withToken($accessToken)
                ->get('https://graph.microsoft.com/v1.0/me', [
                    '$select' => 'mail,userPrincipalName,displayName'
                ]);

            if ($graphResponse->failed()) {
                Log::error('Microsoft Graph failed: ' . $graphResponse->body());
                return redirect("{$frontendUrl}/login?error=graph_fallido");
            }

            $msUser = $graphResponse->json();
            $correo = strtolower(trim($msUser['mail'] ?? $msUser['userPrincipalName'] ?? ''));

            if (empty($correo)) {
                return redirect("{$frontendUrl}/login?error=correo_no_encontrado");
            }

            // 3. Buscar persona por correo en la BD
            $personaCorreo = DB::table('persona_correo')
                ->where('correo', $correo)
                ->first();

            if (!$personaCorreo) {
                return redirect("{$frontendUrl}/login?error=usuario_no_registrado");
            }

            // 4. Buscar usuario del sistema
            $usuario = DB::table('usuario as u')
                ->join('persona as p', 'u.id_persona', '=', 'p.id_persona')
                ->leftJoin('usuario_rol as ur', 'u.id_usuario', '=', 'ur.id_usuario')
                ->leftJoin('rol as r', 'ur.id_rol', '=', 'r.id_rol')
                ->where('u.id_persona', $personaCorreo->id_persona)
                ->select(
                    'u.id_usuario',
                    'u.nombre_usuario',
                    'u.estatus',
                    'p.nombre',
                    'p.apellido_paterno',
                    'p.apellido_materno',
                    'r.id_rol',
                    'r.nombre_rol',
                    'r.clave as rol_clave'
                )
                ->first();

            if (!$usuario) {
                return redirect("{$frontendUrl}/login?error=sin_acceso");
            }

            if (!$usuario->estatus) {
                return redirect("{$frontendUrl}/login?error=cuenta_desactivada");
            }

            // 5. Registrar último acceso + bitácora
            DB::table('usuario')
                ->where('id_usuario', $usuario->id_usuario)
                ->update(['ultimo_acceso' => now()]);

            BitacoraService::registrar(
                'LOGIN',
                'usuario',
                $usuario->id_usuario,
                [],
                ['metodo' => 'microsoft_oauth'],
                $usuario->id_usuario
            );

            // 6. Generar token — mismo formato que AuthController
            $token = base64_encode(
                $usuario->id_usuario . '|' .
                $correo              . '|' .
                time()               . '|' .
                bin2hex(random_bytes(16))
            );

            $nombreCompleto = trim(
                ($usuario->nombre           ?? '') . ' ' .
                ($usuario->apellido_paterno ?? '') . ' ' .
                ($usuario->apellido_materno ?? '')
            );

            // 7. Redirigir al frontend con los datos en query params
            $params = http_build_query([
                'token'          => $token,
                'id_usuario'     => $usuario->id_usuario,
                'nombre_usuario' => $usuario->nombre_usuario,
                'correo'         => $correo,
                'nombre'         => $nombreCompleto,
                'id_rol'         => $usuario->id_rol,
                'nombre_rol'     => $usuario->nombre_rol ?? 'Sin rol',
                'rol'            => $usuario->rol_clave ?? $usuario->nombre_rol ?? 'Sin rol',
            ]);

            return redirect("{$frontendUrl}/auth/callback?{$params}");

        } catch (\Exception $e) {
            Log::error('Microsoft OAuth callback error: ' . $e->getMessage());
            return redirect("{$frontendUrl}/login?error=error_interno");
        }
    }
}