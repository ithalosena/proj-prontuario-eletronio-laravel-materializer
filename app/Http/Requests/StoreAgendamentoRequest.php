<?php

namespace App\Http\Requests;

use App\Models\TipoConsulta;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/*
 * FormRequest: StoreAgendamentoRequest
 *
 * Valida os dados enviados pelo staff (nivel <= 3) ao criar um agendamento.
 * A data/hora deve ser futura ('after:now').
 */
class StoreAgendamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'paciente_id'     => ['required', 'exists:pacientes,id'],
            'profissional_id' => ['required', 'exists:profissionais,id'],
            'data_hora'       => ['required', 'date', 'after:now'],
            'tipo'            => ['required', Rule::in(TipoConsulta::pluck('nome'))],
            'observacao'      => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'paciente_id.required'     => 'Selecione um paciente.',
            'paciente_id.exists'       => 'Paciente inválido.',
            'profissional_id.required' => 'Selecione um profissional.',
            'profissional_id.exists'   => 'Profissional inválido.',
            'data_hora.required'       => 'A data e hora são obrigatórias.',
            'data_hora.after'          => 'O agendamento deve ser para uma data futura.',
            'tipo.required'            => 'O tipo de consulta é obrigatório.',
            'tipo.in'                  => 'Tipo de consulta inválido.',
        ];
    }
}
