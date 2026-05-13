<?php

namespace App\Http\Requests;

use App\Models\TipoConsulta;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/*
 * FormRequest: StoreConsultaRequest
 *
 * Valida os dados enviados pelo formulário de nova consulta.
 * Além dos campos da consulta em si, também valida os arrays de exames e
 * prescrições que podem ser enviados inline no mesmo formulário.
 *
 * O Laravel valida arrays com a notação 'campo.*.subcampo', onde o asterisco (*)
 * representa cada item da lista dinâmica adicionada via JavaScript.
 */
class StoreConsultaRequest extends FormRequest
{
    /*
     * Autoriza todos os usuários autenticados.
     * O controle por nível de acesso é feito nas rotas via middleware CheckNivel.
     */
    public function authorize(): bool
    {
        return true;
    }

    /*
     * Regras de validação.
     * 'nullable' significa que o campo pode vir vazio — o Laravel não rejeita se faltar.
     * Os campos do SOAP (anamnese, diagnostico, conduta) são opcionais porque nem
     * toda consulta precisa preencher todos os campos de uma vez.
     * Os exames e prescrições são arrays nullable porque o usuário pode não adicionar nenhum.
     */
    public function rules(): array
    {
        return [
            // Campos principais da consulta
            'atendimento_id'                   => 'nullable|exists:atendimentos,id',
            'agendamento_id'                   => 'nullable|exists:agendamentos,id',
            'profissional_id'                  => 'required|exists:profissionais,id',
            'paciente_id'                      => 'required|exists:pacientes,id',
            'data_hora'                        => 'required|date',
            'tipo'                             => ['required', Rule::in(TipoConsulta::pluck('nome'))],
            'queixa'                           => 'required|string|max:1000',
            'anamnese'                         => 'nullable|string',
            'diagnostico'                      => 'nullable|string',
            'conduta'                          => 'nullable|string',

            // Exames: o array inteiro é opcional, mas cada item tem seus próprios campos
            'exames'                           => 'nullable|array',
            'exames.*.tipo'                    => 'nullable|string|max:255',
            'exames.*.data_solicitacao'        => 'nullable|date',
            'exames.*.observacao'              => 'nullable|string|max:1000',

            // Prescrições: mesma lógica dos exames
            'prescricoes'                      => 'nullable|array',
            'prescricoes.*.nome_medicamento'   => 'nullable|string|max:255',
            'prescricoes.*.dosagem'            => 'nullable|string|max:100',
            'prescricoes.*.frequencia'         => 'nullable|string|max:100',
            'prescricoes.*.duracao'            => 'nullable|string|max:100',
            'prescricoes.*.observacao'         => 'nullable|string|max:1000',
        ];
    }

    // Mensagens de erro em português para exibição no formulário
    public function messages(): array
    {
        return [
            'profissional_id.required' => 'Selecione um profissional.',
            'profissional_id.exists'   => 'Profissional inválido.',
            'paciente_id.required'     => 'Selecione um paciente.',
            'paciente_id.exists'       => 'Paciente inválido.',
            'data_hora.required'       => 'A data e hora são obrigatórias.',
            'data_hora.date'           => 'Informe uma data e hora válidas.',
            'tipo.required'            => 'O tipo de consulta é obrigatório.',
            'tipo.in'                  => 'Tipo de consulta inválido.',
            'queixa.required'          => 'A queixa principal é obrigatória.',
        ];
    }
}
