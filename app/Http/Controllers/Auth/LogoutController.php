<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class LogoutController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        try {
            JWTAuth::parseToken()->invalidate(); // invalida o token atual
            return $this->success('Logout realizado com sucesso', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->error('Token inválido', Response::HTTP_UNAUTHORIZED);
        }
    }
}
