<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware ST-15 — Wizard de Onboarding de Primeiro Acesso.
 *
 * Ordem no grupo de middlewares: auth → consentimento → onboarding → (nivel)
 * Redireciona para /onboarding se users.onboarding_completo = false.
 * Modelado em CheckConsentimento (v0.8.2).
 */
class CheckOnboarding
{
    // Rotas que não devem ser interceptadas — evita loops de redirect
    private const ROTAS_ISENTAS = [
        '/onboarding',
        '/onboarding/salvar/paciente',
        '/onboarding/salvar/operador',
        '/consentimento',
        '/consentimento/aceitar',
        '/consentimento/recusar',
        '/privacidade',
        '/logout',
        '/session/ping',
        '/perfil',
        '/perfil/avatar',
        '/perfil/avatar/remover',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Usuário não autenticado → auth já tratou
        if (!Auth::check()) {
            return $next($request);
        }

        // Rotas isentas → passa adiante
        if (\in_array('/' . $request->path(), self::ROTAS_ISENTAS, true)) {
            return $next($request);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Onboarding já completo → passa adiante
        if ($user->onboarding_completo) {
            return $next($request);
        }

        // Determina o tipo de wizard conforme o nível de acesso
        $nivel = $user->nivelAcesso();

        if ($nivel === 5 && $user->paciente) {
            $tipoWizard = 'paciente';
        } elseif ($nivel >= 1 && $nivel <= 4) {
            $tipoWizard = 'operador';
        } else {
            // Perfil incompleto (sem role ou sem vínculo) — não bloquear
            return $next($request);
        }

        // Armazena na sessão para que OnboardingController saiba qual view exibir
        session(['tipo_wizard_onboarding' => $tipoWizard]);

        return redirect('/onboarding');
    }
}
