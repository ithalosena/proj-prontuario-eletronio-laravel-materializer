<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/*
 * Controller: MeusDadosController (C.5, v0.10.3)
 *
 * Tela "Meus Dados" — dados complementares editáveis pelo próprio usuário,
 * separada de /perfil (que cuida só de conta: nome, e-mail read-only, senha, avatar).
 *
 * - Paciente: endereço, contatos, complementares, emergência e dados de saúde
 *   (autorrelatados — o paciente edita os próprios, com auditoria via AuditObserver).
 * - Profissional: contato e registro profissional.
 * - Operadores (admin/coord/recep) não têm dados complementares → redireciona a /perfil.
 *
 * Rotas (grupo auth):
 *   GET  /meus-dados → edit()
 *   PUT  /meus-dados → update()
 */
class MeusDadosController extends Controller
{
    public function edit()
    {
        // C.5 (v0.10.3+): "Meus Dados" foi unificado em /perfil. Mantém a rota por compatibilidade,
        // redirecionando para o perfil (onde os dados complementares agora vivem por role).
        return redirect('/perfil');
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        // Paciente edita os próprios dados (auditados pelo AuditObserver).
        // v0.10.3+: salvamento POR SEÇÃO — valida só os campos da seção enviada (evita travar
        // o resto por causa de um bloco). Responde JSON quando chamado via AJAX (fetch).
        if ($user->paciente) {
            $dados = $request->validate($this->regrasPacientePorSecao($user, $request->input('secao')), $this->mensagensPaciente());
            $user->paciente->update($dados);
            if ($request->expectsJson()) {
                return response()->json(['ok' => true]);
            }
            return redirect('/perfil')->with('success', 'Seus dados foram atualizados.');
        }

        // Profissional edita contato e registro
        if ($user->profissional) {
            $dados = $request->validate([
                'contato'               => ['nullable', 'string', 'max:20'],
                'registro_profissional' => ['nullable', 'string', 'max:100'],
            ]);
            $user->profissional->update($dados);
            if ($request->expectsJson()) {
                return response()->json(['ok' => true]);
            }
            return redirect('/perfil')->with('success', 'Seus dados foram atualizados.');
        }

        return redirect('/perfil');
    }

    /*
     * v0.10.3+: filtra as regras completas do paciente para apenas os campos da seção enviada.
     * Se a seção não for reconhecida (ou vier vazia), valida tudo (comportamento antigo).
     */
    private function regrasPacientePorSecao($user, ?string $secao): array
    {
        $todas = $this->regrasPaciente($user);

        $mapa = [
            'sobre'    => ['nome_social', 'naturalidade_cidade', 'naturalidade_uf', 'raca_cor', 'estado_civil', 'nome_mae'],
            'endereco' => ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf', 'ponto_referencia'],
            'contatos' => [
                'contato', 'telefone_alternativo', 'email_alternativo',
                'contato_emergencia_nome', 'contato_emergencia_telefone', 'contato_emergencia_parentesco',
                'contato_emergencia2_nome', 'contato_emergencia2_telefone', 'contato_emergencia2_parentesco',
                'responsavel_nome', 'responsavel_telefone', 'responsavel_email', 'responsavel_parentesco',
            ],
            'saude'    => [
                'tipo_sanguineo', 'peso_kg', 'altura_cm', 'alergias', 'medicamentos_uso_continuo',
                'condicoes_cronicas', 'cirurgias_previas', 'tabagismo', 'etilismo', 'atividade_fisica',
            ],
        ];

        return isset($mapa[$secao]) ? array_intersect_key($todas, array_flip($mapa[$secao])) : $todas;
    }

    /*
     * Regras do paciente: mesmas do OnboardingPacienteRequest, SEM os campos de identidade
     * (nome, documento, data_nascimento, sexo — imutáveis fora do onboarding) e sem os
     * institucionais (e-mail, matrícula, curso).
     */
    private function regrasPaciente($user): array
    {
        $isMenor = $user->paciente && $user->paciente->data_nascimento->age < 18;

        return [
            // Endereço
            'cep'         => ['required', 'string', 'max:9'],
            'logradouro'  => ['required', 'string', 'max:255'],
            'numero'      => ['required', 'string', 'max:20'],
            'complemento' => ['nullable', 'string', 'max:100'],
            'bairro'      => ['required', 'string', 'max:100'],
            'cidade'      => ['required', 'string', 'max:100'],
            'uf'          => ['required', 'string', 'size:2'],
            'ponto_referencia' => ['nullable', 'string', 'max:255'],

            // Contatos
            'contato'              => ['required', 'string', 'max:20'],
            'telefone_alternativo' => ['nullable', 'string', 'max:20'],
            'email_alternativo'    => ['nullable', 'email', 'max:255',
                function ($attr, $value, $fail) use ($user) {
                    if ($value && $value === $user->email) {
                        $fail('O e-mail alternativo não pode ser igual ao e-mail institucional.');
                    }
                },
            ],

            // Complementares (opcionais)
            'nome_social'         => ['nullable', 'string', 'max:255'],
            'naturalidade_cidade' => ['nullable', 'string', 'max:100'],
            'naturalidade_uf'     => ['nullable', 'string', 'size:2'],
            'raca_cor'            => ['nullable', Rule::in(['branca', 'preta', 'parda', 'amarela', 'indigena', 'nao_declarado'])],
            'estado_civil'        => ['nullable', Rule::in(['solteiro', 'casado', 'divorciado', 'viuvo', 'uniao_estavel'])],
            'nome_mae'            => ['nullable', 'string', 'max:255'],

            // Emergência principal (obrigatório)
            'contato_emergencia_nome'       => ['required', 'string', 'max:255'],
            'contato_emergencia_telefone'   => ['required', 'string', 'max:20'],
            'contato_emergencia_parentesco' => ['required', 'string', 'max:50'],

            // Emergência secundária (atômica)
            'contato_emergencia2_nome'       => ['nullable', 'string', 'max:255', 'required_with:contato_emergencia2_telefone,contato_emergencia2_parentesco'],
            'contato_emergencia2_telefone'   => ['nullable', 'string', 'max:20', 'required_with:contato_emergencia2_nome,contato_emergencia2_parentesco'],
            'contato_emergencia2_parentesco' => ['nullable', 'string', 'max:50', 'required_with:contato_emergencia2_nome,contato_emergencia2_telefone'],

            // Responsável legal (condicional a menoridade)
            'responsavel_nome'       => [$isMenor ? 'required' : 'nullable', 'string', 'max:255'],
            // responsavel_cpf removido do fluxo (v0.10.5) — coluna preservada, mas sem uso
            'responsavel_telefone'   => [$isMenor ? 'required' : 'nullable', 'string', 'max:20'],
            'responsavel_email'      => [$isMenor ? 'required' : 'nullable', 'email', 'max:255'],
            'responsavel_parentesco' => [$isMenor ? 'required' : 'nullable', 'string', 'max:50'],

            // Dados de saúde (autorrelatados, opcionais)
            'tipo_sanguineo'   => ['nullable', Rule::in(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'NS'])],
            'peso_kg'          => ['nullable', 'numeric', 'min:1', 'max:300'],
            'altura_cm'        => ['nullable', 'integer', 'min:30', 'max:250'],
            'alergias'                  => ['nullable', 'string'],
            'medicamentos_uso_continuo' => ['nullable', 'string'],
            'condicoes_cronicas'        => ['nullable', 'string'],
            'cirurgias_previas'         => ['nullable', 'string'],
            'tabagismo'        => ['nullable', Rule::in(['nao', 'ex_fumante', 'sim'])],
            'etilismo'         => ['nullable', Rule::in(['nao', 'ocasional', 'frequente'])],
            'atividade_fisica' => ['nullable', Rule::in(['sedentario', 'leve', 'moderada'])],
        ];
    }

    private function mensagensPaciente(): array
    {
        return [
            'contato.required'                       => 'Informe o telefone principal.',
            'contato_emergencia_nome.required'       => 'Informe o nome do contato de emergência.',
            'contato_emergencia_telefone.required'   => 'Informe o telefone do contato de emergência.',
            'contato_emergencia_parentesco.required' => 'Informe o parentesco do contato de emergência.',
            'responsavel_nome.required'       => 'Informe o nome do responsável legal.',
            'responsavel_telefone.required'   => 'Informe o telefone do responsável legal.',
            'responsavel_email.required'      => 'Informe o e-mail do responsável legal.',
            'responsavel_parentesco.required' => 'Informe o parentesco do responsável legal.',
        ];
    }
}
