<?php

namespace App\Http\Controllers;

use App\Http\Middleware\CheckConsentimento;
use App\Models\AuditLog;
use App\Models\Consentimento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// v0.8.2: expandido para titular (paciente) e operador (profissional/admin/recepção)
class ConsentimentoController extends Controller
{
    // Exibe a tela de consentimento correta conforme o tipo armazenado na sessão pelo middleware
    public function show()
    {
        $user = Auth::user();

        // O middleware armazena o tipo pendente na sessão antes de redirecionar para esta rota
        $tipoTermo = session('tipo_termo_pendente', 'titular');

        // Se já consentiu com esta versão e tipo (e não revogou), redireciona para o destino correto
        $jaConsentiu = Consentimento::where('user_id', $user->id)
            ->where('versao_termo', CheckConsentimento::VERSAO_ATUAL)
            ->where('tipo_termo', $tipoTermo)
            ->whereNull('revogado_em')
            ->exists();

        if ($jaConsentiu) {
            return $tipoTermo === 'titular' ? redirect('/meu-prontuario') : redirect('/');
        }

        return view('content.pages.consentimento', [
            'versaoTermo' => CheckConsentimento::VERSAO_ATUAL,
            'tipoTermo'   => $tipoTermo,
        ]);
    }

    // Registra o aceite explícito (LGPD Art. 5º, XII — manifestação livre, informada e inequívoca)
    public function aceitar(Request $request)
    {
        $user = Auth::user();

        // tipo_termo vem do campo oculto no formulário; fallback para sessão ou 'titular'
        $tipoTermo = $request->input('tipo_termo', session('tipo_termo_pendente', 'titular'));

        // Sanitiza — apenas valores válidos
        if (!in_array($tipoTermo, ['titular', 'operador'])) {
            $tipoTermo = 'titular';
        }

        // Verifica se já existe um aceite ativo (não revogado) — idempotente
        // firstOrCreate não serve aqui: encontraria registro com revogado_em preenchido
        // e não criaria novo, mantendo o middleware em loop após revogação
        $jaAceito = Consentimento::where('user_id', $user->id)
            ->where('versao_termo', CheckConsentimento::VERSAO_ATUAL)
            ->where('tipo_termo', $tipoTermo)
            ->whereNull('revogado_em')
            ->exists();

        if (!$jaAceito) {
            Consentimento::create([
                'user_id'      => $user->id,
                'versao_termo' => CheckConsentimento::VERSAO_ATUAL,
                'tipo_termo'   => $tipoTermo,
                'ip_address'   => $request->ip(),
                'user_agent'   => $request->userAgent(),
            ]);
        }

        session()->forget('tipo_termo_pendente');

        // Todos os perfis vão para a home após aceitar
        $destino = '/';
        return redirect($destino)->with('success', 'Consentimento registrado. Bem-vindo ao Prontu IF!');
    }

    // Registra recusa e desloga imediatamente — recusa vai para audit_log, não para consentimentos
    public function recusar(Request $request)
    {
        $user = Auth::user();

        // A tabela consentimentos armazena apenas aceites válidos — recusa fica no audit_log
        AuditLog::registrar(
            action: 'consentimento_recusado',
            userId: $user->id,
        );

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')
            ->with('warning', 'Você não aceitou os termos de uso. Para acessar o Prontu IF, é necessário aceitar o termo de consentimento.');
    }

    // Revogação do consentimento do titular (LGPD Art. 8º §5º — "a qualquer momento")
    public function revogar(Request $request)
    {
        $user = Auth::user();

        // Marca revogado_em nos aceites ativos do titular — histórico permanece intacto
        Consentimento::where('user_id', $user->id)
            ->where('tipo_termo', 'titular')
            ->whereNull('revogado_em')
            ->update(['revogado_em' => now()]);

        AuditLog::registrar(
            action: 'consentimento_revogado',
            userId: $user->id,
        );

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')
            ->with('warning', 'Seu consentimento foi revogado. Você não poderá acessar o prontuário eletrônico até novo aceite. Em caso de dúvidas, entre em contato com o Setor de Saúde.');
    }
}
