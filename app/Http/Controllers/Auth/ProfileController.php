<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $userId = $request->attributes->get('user_id');

        $user = User::query()
            ->select('id', 'name', 'email', 'cpf', 'phone', 'birthday')
            ->where('id', $userId)
            ->firstOrFail();

        Log::info('ProfileController', ['user' => $user]);

        if (!$user) {
            return $this->error('Usuário não encontrado', Response::HTTP_NOT_FOUND);
        }

        return $this->success('Seu perfil', Response::HTTP_OK, [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'birthday' => $user->birthday?->format('Y-m-d'),
                'cpf' => $user->cpf,
                'phone' => $user->phone,
            ]
        ]);
    }
}
