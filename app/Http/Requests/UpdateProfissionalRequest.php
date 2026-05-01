<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfissionalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome'                  => 'required|string|max:255',
            'email'                 => 'nullable|email',
            'especialidade'         => 'required|string|max:255',
            'registro_profissional' => 'required|string|max:50',
            'contato'               => 'nullable|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required'                  => 'O nome é obrigatório.',
            'email.email'                    => 'Informe um e-mail válido.',
            'especialidade.required'         => 'A especialidade é obrigatória.',
            'registro_profissional.required' => 'O registro profissional é obrigatório.',
        ];
    }
}
