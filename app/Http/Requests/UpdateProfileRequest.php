<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // qualquer usuário autenticado pode atualizar o próprio perfil
    }

    public function rules(): array
    {
        return [
            // v0.10.3+: nome e e-mail são institucionais (imutáveis no perfil) — não validados aqui.
            // O perfil só altera a senha; o nome de exibição do paciente é o "nome social" (Meus Dados).
            'current_password' => ['required_with:new_password', 'nullable', 'string'],
            'new_password'     => ['nullable', 'string', 'confirmed', Password::min(8)],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required_with' => 'Informe a senha atual para definir uma nova senha.',
            'new_password.confirmed'    => 'A confirmação da nova senha não confere.',
            'new_password.min'          => 'A nova senha deve ter no mínimo 8 caracteres.',
        ];
    }
}
