<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProfissionalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email',
            'senha'                 => 'required|string|min:8',
            'especialidade'         => 'required|string|max:255',
            'registro_profissional' => 'required|string|max:50',
            'contato'               => 'nullable|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required'                  => 'O nome é obrigatório.',
            'email.required'                 => 'O e-mail é obrigatório.',
            'email.email'                    => 'Informe um e-mail válido.',
            'email.unique'                   => 'Este e-mail já está em uso.',
            'senha.required'                 => 'A senha é obrigatória.',
            'senha.min'                      => 'A senha deve ter no mínimo 8 caracteres.',
            'especialidade.required'         => 'A especialidade é obrigatória.',
            'registro_profissional.required' => 'O registro profissional é obrigatório.',
        ];
    }
}
