<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConsultaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data_hora'   => 'required|date',
            'tipo'        => 'required|string|max:100',
            'queixa'      => 'required|string|max:1000',
            'anamnese'    => 'nullable|string',
            'diagnostico' => 'nullable|string',
            'conduta'     => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'data_hora.required' => 'A data e hora são obrigatórias.',
            'data_hora.date'     => 'Informe uma data e hora válidas.',
            'tipo.required'      => 'O tipo de consulta é obrigatório.',
            'queixa.required'    => 'A queixa principal é obrigatória.',
        ];
    }
}
