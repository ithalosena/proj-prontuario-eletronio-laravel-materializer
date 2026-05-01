<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePacienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome'            => 'required|string|max:255',
            'email'           => 'required|email|unique:users,email',
            'senha'           => 'required|string|min:8',
            'documento'       => 'required|string|unique:pacientes,documento',
            'data_nascimento' => 'required|date|before:today',
            'sexo'            => 'required|in:M,F,outro',
            'matricula'       => 'required|string|unique:pacientes,matricula',
            'curso'           => 'required|string|max:255',
            'contato'         => 'nullable|string|max:20',
            'endereco'        => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required'            => 'O nome é obrigatório.',
            'email.required'           => 'O e-mail é obrigatório.',
            'email.email'              => 'Informe um e-mail válido.',
            'email.unique'             => 'Este e-mail já está em uso.',
            'senha.required'           => 'A senha é obrigatória.',
            'senha.min'                => 'A senha deve ter no mínimo 8 caracteres.',
            'documento.required'       => 'O documento é obrigatório.',
            'documento.unique'         => 'Este documento já está cadastrado.',
            'data_nascimento.required' => 'A data de nascimento é obrigatória.',
            'data_nascimento.date'     => 'Informe uma data válida.',
            'data_nascimento.before'   => 'A data de nascimento deve ser anterior a hoje.',
            'sexo.required'            => 'O sexo é obrigatório.',
            'sexo.in'                  => 'O sexo deve ser M, F ou outro.',
            'matricula.required'       => 'A matrícula é obrigatória.',
            'matricula.unique'         => 'Esta matrícula já está cadastrada.',
            'curso.required'           => 'O curso é obrigatório.',
        ];
    }
}
