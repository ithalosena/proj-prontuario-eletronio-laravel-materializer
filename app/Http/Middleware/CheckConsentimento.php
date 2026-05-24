<?php

namespace App\Http\Middleware;

use App\Models\Consentimento;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// v0.8.2: middleware expandido para cobrir todos os perfis (1–5)
// Pacientes → tipo_termo 'titular' (Art. 11, I — dados sensíveis de saúde)
// Profissionais, recepcionistas, admins → tipo_termo 'operador' (Art. 47 — agente de tratamento)
class CheckConsentimento
{
    // Versão atual do termo — incrementar aqui força novo aceite de todos os usuários
    const VERSAO_ATUAL = '1.0';

    // Rotas que o usuário precisa acessar antes ou durante o processo de consentimento
    private const ROTAS_ISENTAS = [
        '/consentimento',
        '/consentimento/aceitar',
        '/consentimento/recusar',
        '/privacidade',
        '/logout',
    ];

    public function handle(Request $request, Closure $next): mixed
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        // Verifica se a rota atual está isenta do middleware
        foreach (self::ROTAS_ISENTAS as $rota) {
            if ($request->is(ltrim($rota, '/'))) {
                return $next($request);
            }
        }

        // Determina o tipo de termo conforme nível de acesso do usuário
        $nivel = $user->nivelAcesso();

        if ($nivel === 5 && $user->paciente) {
            $tipoTermo = 'titular';
        } elseif (in_array($nivel, [1, 2, 3, 4])) {
            $tipoTermo = 'operador';
        } else {
            // Usuário autenticado sem perfil vinculado — deixa passar
            return $next($request);
        }

        // Verifica se já consentiu com a versão e tipo corretos, e não revogou
        $consentiu = Consentimento::where('user_id', $user->id)
            ->where('versao_termo', self::VERSAO_ATUAL)
            ->where('tipo_termo', $tipoTermo)
            ->whereNull('revogado_em')
            ->exists();

        if (!$consentiu) {
            // Armazena tipo na sessão para o controller exibir o termo correto
            session(['tipo_termo_pendente' => $tipoTermo]);
            return redirect('/consentimento');
        }

        return $next($request);
    }
}
