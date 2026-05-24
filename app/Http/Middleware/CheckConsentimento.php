<?php

namespace App\Http\Middleware;

use App\Models\Consentimento;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckConsentimento
{
    // Versão atual do termo — incrementar aqui força novo aceite de todos os pacientes
    const VERSAO_ATUAL = '1.0';

    // Rotas isentas: o paciente precisa poder acessar o termo antes de aceitá-lo
    private const ROTAS_ISENTAS = [
        '/consentimento',
        '/consentimento/aceitar',
        '/privacidade',
        '/logout',
    ];

    public function handle(Request $request, Closure $next): mixed
    {
        $user = Auth::user();

        // Só aplica para pacientes (nivel 5 com perfil de paciente)
        if (!$user || $user->nivelAcesso() !== 5 || !$user->paciente) {
            return $next($request);
        }

        // Verifica se a rota atual está isenta
        foreach (self::ROTAS_ISENTAS as $rota) {
            if ($request->is(ltrim($rota, '/'))) {
                return $next($request);
            }
        }

        // Verifica se o paciente já consentiu com a versão atual
        $consentiu = Consentimento::where('user_id', $user->id)
            ->where('versao_termo', self::VERSAO_ATUAL)
            ->exists();

        if (!$consentiu) {
            return redirect('/consentimento');
        }

        return $next($request);
    }
}
