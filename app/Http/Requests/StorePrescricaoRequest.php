<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePrescricaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'consulta_id'      => 'required|exists:consultas,id',
            'nome_medicamento' => 'required|string|max:255',
            'dosagem'          => 'required|string|max:100',
            'frequencia'       => 'required|string|max:100',
            'duracao'          => 'required|string|max:100',
            'observacao'       => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'consulta_id.required'      => 'A consulta é obrigatória.',
            'consulta_id.exists'        => 'A consulta selecionada não existe.',
            'nome_medicamento.required' => 'O nome do medicamento é obrigatório.',
            'dosagem.required'          => 'A dosagem é obrigatória.',
            'frequencia.required'       => 'A frequência é obrigatória.',
            'duracao.required'          => 'A duração é obrigatória.',
        ];
    }
}
