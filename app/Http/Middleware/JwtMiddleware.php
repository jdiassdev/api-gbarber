<?php

namespace App\Http\Middleware;

use Closure;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            //parseToken() -> pega o token do header Authorization
            //authenticate() -> autentica o token e retorna o usuário
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token inválido ou ausente'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // adiciona o usuário autenticado à requisição para uso posterior
        // qualquer controller com middleware 'jwt.auth' poderá acessar o usuário autenticado via $request->user
        $request->merge(['user' => $user]);

        return $next($request);
    }
}
