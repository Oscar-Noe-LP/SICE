<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * POST /api/login
     */
    public function login(Request $request)
    {
        $request->validate([
            'nombre_usuario' => 'required|string',
            'contrasena'     => 'required|string',
        ]);

        $usuario = Usuario::with(['persona', 'roles'])
            ->where('nombre_usuario', $request->nombre_usuario)
            ->first();

        if (!$usuario || !Hash::check($request->contrasena, $usuario->password_hash)) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales incorrectas',
            ], 401);
        }

        if (!$usuario->estatus) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario inactivo',
            ], 403);
        }

        $token = $usuario->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token'   => $token,
            'usuario' => $this->formatUsuario($usuario),
        ]);
    }

    /**
     * POST /api/logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['success' => true, 'message' => 'Sesión cerrada']);
    }

    /**
     * GET /api/me
     */
    public function me(Request $request)
    {
        $usuario = Usuario::with(['persona', 'roles'])
            ->find($request->user()->id_usuario);

        return response()->json([
            'success' => true,
            'usuario' => $this->formatUsuario($usuario),
        ]);
    }

    private function formatUsuario(Usuario $u): array
    {
        $persona = $u->persona;

        return [
            'id_usuario'      => $u->id_usuario,
            'nombre_usuario'  => $u->nombre_usuario,
            'estatus'         => $u->estatus ? 'Activo' : 'Inactivo',
            'nombre_completo' => $persona
                ? trim("{$persona->nombre} {$persona->apellido_paterno} {$persona->apellido_materno}")
                : $u->nombre_usuario,
            'roles' => $u->roles ? $u->roles->pluck('nombre_rol') : [],
        ];
    }
}
