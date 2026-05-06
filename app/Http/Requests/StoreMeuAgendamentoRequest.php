<?php

namespace App\Http\Requests;

use App\Models\TipoConsulta;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/*
 * FormRequest: StoreMeuAgendamentoRequest
 *
 * Valida os dados enviados pelo paciente ao criar o próprio agendamento.
 * Regra crítica de segurança: paciente_id deve ser o do usuário logado —
 * impede que um paciente crie agendamentos em nome de outro.
 */
class StoreMeuAgendamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $pacienteId = Auth::user()->paciente?->id ?? 0;

        return [
            // Garante que o paciente só pode agendar para si mesmo
            'paciente_id'     => ['required', Rule::in([$pacienteId])],
            'profissional_id' => ['required', 'exists:profissionais,id'],
            'data_hora'       => ['required', 'date', 'after:now'],
            'tipo'            => ['required', Rule::in(TipoConsulta::pluck('nome'))],
            'observacao'      => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'paciente_id.in'           => 'Operação não autorizada.',
            'profissional_id.required' => 'Selecione um profissional.',
            'profissional_id.exists'   => 'Profissional inválido.',
            'data_hora.required'       => 'A data e hora são obrigatórias.',
            'data_hora.after'          => 'O agendamento deve ser para uma data futura.',
            'tipo.required'            => 'O tipo de consulta é obrigatório.',
            'tipo.in'                  => 'Tipo de consulta inválido.',
        ];
    }
}
