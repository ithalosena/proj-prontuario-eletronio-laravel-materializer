<?php

namespace App\Http\Controllers;

use App\Http\Middleware\CheckConsentimento;
use App\Models\Consentimento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConsentimentoController extends Controller
{
    // Exibe a tela de consentimento (LGPD Art. 11, I)
    public function show()
    {
        $user = Auth::user();

        // Redireciona se não é paciente ou já consentiu
        if (!$user->paciente) {
            return redirect('/');
        }

        $jaConsentiu = Consentimento::where('user_id', $user->id)
            ->where('versao_termo', CheckConsentimento::VERSAO_ATUAL)
            ->exists();

        if ($jaConsentiu) {
            return redirect('/meu-prontuario');
        }

        return view('content.pages.consentimento', [
            'versaoTermo' => CheckConsentimento::VERSAO_ATUAL,
        ]);
    }

    // Registra o aceite explícito do paciente (LGPD Art. 5º, XII)
    public function aceitar(Request $request)
    {
        $user = Auth::user();

        if (!$user->paciente) {
            return redirect('/');
        }

        // Idempotente: não duplica se já existir registro para esta versão
        Consentimento::firstOrCreate(
            [
                'user_id'     => $user->id,
                'versao_termo' => CheckConsentimento::VERSAO_ATUAL,
            ],
            [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        return redirect('/meu-prontuario')
            ->with('success', 'Consentimento registrado. Bem-vindo ao Prontu IF!');
    }
}
