<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreConsultaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'profissional_id' => 'required|exists:profissionais,id',
            'paciente_id'     => 'required|exists:pacientes,id',
            'data_hora'       => 'required|date',
            'tipo'            => 'required|string|max:100',
            'queixa'          => 'required|string|max:1000',
            'anamnese'        => 'nullable|string',
            'diagnostico'     => 'nullable|string',
            'conduta'         => 'nullable|string',
        ];
    }

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
            'queixa.required'          => 'A queixa principal é obrigatória.',
        ];
    }
}
