<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $user = $request->user;

        return $this->success('Seu perfil', 200, [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'birthday' => $user->birthday->format('d/m/Y'),
                'cpf' => $user->cpf ?? null,
                'phone' => $user->phone ?? null,
            ]
        ]);
    }
}
