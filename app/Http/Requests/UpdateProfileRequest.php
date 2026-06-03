<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // qualquer usuário autenticado pode atualizar o próprio perfil
    }

    public function rules(): array
    {
        return [
            'name'             => ['required', 'string', 'max:255'],
            // Unique excluindo o próprio usuário para não conflitar com e-mail atual
            'email'            => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user()->id)],
            // Senha: todos os campos opcionais — só valida se new_password for enviado
            'current_password' => ['required_with:new_password', 'nullable', 'string'],
            'new_password'     => ['nullable', 'string', 'confirmed', Password::min(8)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'             => 'O nome é obrigatório.',
            'email.required'            => 'O e-mail é obrigatório.',
            'email.unique'              => 'Este e-mail já está em uso.',
            'current_password.required_with' => 'Informe a senha atual para definir uma nova senha.',
            'new_password.confirmed'    => 'A confirmação da nova senha não confere.',
            'new_password.min'          => 'A nova senha deve ter no mínimo 8 caracteres.',
        ];
    }
}
