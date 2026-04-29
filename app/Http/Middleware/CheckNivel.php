<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckNivel
{
    /**
     * Verifica se o usuario autenticado possui nivel de acesso suficiente.
     * Menor nivel = maior privilegio (0 = superadmin, 5 = paciente).
     */
    public function handle(Request $request, Closure $next, int $nivelMinimo): mixed
    {
        $user = Auth::user();

        if (!$user || $user->nivelAcesso() > $nivelMinimo) {
            // redirect()->back() evita o problema de redirecionar para / que pode quebrar
            // dependendo do perfil do usuário — melhor voltar com mensagem de erro clara
            return redirect()->back()->with('error', 'Você não tem permissão para acessar esta página.');
        }

        return $next($request);
    }
}
