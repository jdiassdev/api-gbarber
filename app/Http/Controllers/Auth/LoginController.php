<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(LoginRequest $request)
    {
        $data = $request->validated();

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return $this->error('Credenciais inválidas', Response::HTTP_UNAUTHORIZED);
        }

        $token = JWTAuth::fromUser($user);

        return $this->success('Login realizado com sucesso', Response::HTTP_OK, [
            'token' => $token,
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ]
        ]);
    }
}
