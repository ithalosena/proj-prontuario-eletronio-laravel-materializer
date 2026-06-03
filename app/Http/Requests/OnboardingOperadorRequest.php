<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OnboardingOperadorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user  = auth()->user();
        $nivel = $user->nivelAcesso();

        $rules = [
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
        ];

        // Profissional (nivel 3) — campos adicionais da tabela profissionais
        if ($nivel === 3) {
            $rules['especialidade']          = ['required', 'string', 'max:255'];
            $rules['registro_profissional']  = ['required', 'string', 'max:100'];
            $rules['contato_profissional']   = ['required', 'string', 'max:20'];
        }

        return $rules;
    }
}
