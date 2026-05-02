<?php

namespace App\Http\Requests;

use App\Models\Especialidade;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'especialidade'         => ['required', Rule::in(Especialidade::pluck('nome'))],
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
            'especialidade.in'               => 'Especialidade inválida.',
            'registro_profissional.required' => 'O registro profissional é obrigatório.',
        ];
    }
}
