<?php

namespace App\Http\Controllers;

use App\Http\Requests\OnboardingPacienteRequest;
use App\Http\Requests\OnboardingOperadorRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Controller do wizard de onboarding de primeiro acesso (ST-15).
 *
 * Disponível para qualquer usuário autenticado com onboarding_completo = false.
 * Dispatch de view por tipo_wizard guardado na sessão pelo CheckOnboarding.
 */
class OnboardingController extends Controller
{
    // =========================================================
    // GET /onboarding — exibe o wizard correto por role
    // =========================================================

    public function show(): View|RedirectResponse
    {
        $user = Auth::user();

        // Já completou → redireciona para home
        if ($user->onboarding_completo) {
            return redirect('/');
        }

        $tipo = session('tipo_wizard_onboarding', $this->detectarTipo($user));

        if ($tipo === 'paciente') {
            return view('content.pages.onboarding_paciente', [
                'user'     => $user,
                'paciente' => $user->paciente,
                'isMenor'  => $user->paciente && $user->paciente->data_nascimento->age < 18,
            ]);
        }

        // Operador (nivels 1–4)
        return view('content.pages.onboarding_operador', [
            'user'         => $user,
            'profissional' => $user->profissional,
            'nivel'        => $user->nivelAcesso(),
        ]);
    }

    // =========================================================
    // POST /onboarding/salvar — persiste dados e conclui wizard
    // =========================================================

    public function salvarPaciente(OnboardingPacienteRequest $request): RedirectResponse
    {
        $user     = Auth::user();
        $paciente = $user->paciente;

        // Atualiza os campos editáveis do passo 0 no paciente
        $paciente->update([
            'nome'           => $request->nome,
            'documento'      => $request->documento,
            'data_nascimento'=> $request->data_nascimento,
            'sexo'           => $request->sexo,
            // Passo 1
            'cep'            => $request->cep,
            'logradouro'     => $request->logradouro,
            'numero'         => $request->numero,
            'complemento'    => $request->complemento,
            'bairro'         => $request->bairro,
            'cidade'         => $request->cidade,
            'uf'             => $request->uf,
            'ponto_referencia' => $request->ponto_referencia,
            // Passo 2
            'contato'              => $request->contato,
            'telefone_alternativo' => $request->telefone_alternativo,
            'email_alternativo'    => $request->email_alternativo,
            // Passo 3
            'nome_social'         => $request->nome_social,
            'naturalidade_cidade' => $request->naturalidade_cidade,
            'naturalidade_uf'     => $request->naturalidade_uf,
            'raca_cor'            => $request->raca_cor,
            'estado_civil'        => $request->estado_civil,
            'nome_mae'            => $request->nome_mae,
            // Passo 4
            'contato_emergencia_nome'       => $request->contato_emergencia_nome,
            'contato_emergencia_telefone'   => $request->contato_emergencia_telefone,
            'contato_emergencia_parentesco' => $request->contato_emergencia_parentesco,
            'contato_emergencia2_nome'       => $request->contato_emergencia2_nome,
            'contato_emergencia2_telefone'   => $request->contato_emergencia2_telefone,
            'contato_emergencia2_parentesco' => $request->contato_emergencia2_parentesco,
            'responsavel_nome'       => $request->responsavel_nome,
            'responsavel_telefone'   => $request->responsavel_telefone,
            'responsavel_email'      => $request->responsavel_email,
            'responsavel_parentesco' => $request->responsavel_parentesco,
            // Passo 5
            'tipo_sanguineo'            => $request->tipo_sanguineo,
            'peso_kg'                   => $request->peso_kg,
            'altura_cm'                 => $request->altura_cm,
            'alergias'                  => $request->alergias,
            'medicamentos_uso_continuo' => $request->medicamentos_uso_continuo,
            'condicoes_cronicas'        => $request->condicoes_cronicas,
            'cirurgias_previas'         => $request->cirurgias_previas,
            'tabagismo'                 => $request->tabagismo,
            'etilismo'                  => $request->etilismo,
            'atividade_fisica'          => $request->atividade_fisica,
        ]);

        // Conclui o onboarding — libera acesso ao sistema
        $user->update(['onboarding_completo' => true]);
        session()->forget('tipo_wizard_onboarding');

        return redirect('/')->with('success', 'Bem-vindo ao Prontu IF! Seu cadastro está completo.');
    }

    public function salvarOperador(OnboardingOperadorRequest $request): RedirectResponse
    {
        $user = Auth::user();

        $user->update([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        // Profissional: atualiza campos da tabela profissionais
        if ($user->nivelAcesso() === 3 && $user->profissional) {
            $user->profissional->update([
                'especialidade'         => $request->especialidade,
                'registro_profissional' => $request->registro_profissional,
                'contato'               => $request->contato_profissional,
            ]);
        }

        $user->update(['onboarding_completo' => true]);
        session()->forget('tipo_wizard_onboarding');

        return redirect('/')->with('success', 'Dados confirmados. Bem-vindo!');
    }

    // =========================================================
    // Helpers
    // =========================================================

    private function detectarTipo($user): string
    {
        $nivel = $user->nivelAcesso();
        return ($nivel === 5 && $user->paciente) ? 'paciente' : 'operador';
    }
}
