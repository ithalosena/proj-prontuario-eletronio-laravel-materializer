<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'consulta_id'      => 'required|exists:consultas,id',
            'tipo'             => 'required|string|max:255',
            'data_solicitacao' => 'required|date',
            'data_resultado'   => 'nullable|date|after_or_equal:data_solicitacao',
            'resultado'        => 'nullable|string',
            'observacao'       => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'consulta_id.required'       => 'A consulta é obrigatória.',
            'consulta_id.exists'         => 'A consulta selecionada não existe.',
            'tipo.required'              => 'O tipo de exame é obrigatório.',
            'data_solicitacao.required'  => 'A data de solicitação é obrigatória.',
            'data_solicitacao.date'      => 'Informe uma data válida.',
            'data_resultado.date'        => 'Informe uma data de resultado válida.',
            'data_resultado.after_or_equal' => 'A data do resultado deve ser igual ou posterior à solicitação.',
        ];
    }
}
