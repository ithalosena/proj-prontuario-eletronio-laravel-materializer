<?php

namespace App\Http\Requests;

use App\Models\TipoConsulta;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'tipo'        => ['required', Rule::in(TipoConsulta::pluck('nome'))],
            'queixa'      => 'required|string|max:1000',
            'anamnese'    => 'nullable|string',
            'diagnostico' => 'nullable|string',
            'conduta'     => 'nullable|string',
            'anotacoes'   => 'nullable|string', // E3d: registro livre (outros perfis)
        ];
    }

    public function messages(): array
    {
        return [
            'data_hora.required' => 'A data e hora são obrigatórias.',
            'data_hora.date'     => 'Informe uma data e hora válidas.',
            'tipo.required'      => 'O tipo de consulta é obrigatório.',
            'tipo.in'            => 'Tipo de consulta inválido.',
            'queixa.required'    => 'A queixa principal é obrigatória.',
        ];
    }
}
