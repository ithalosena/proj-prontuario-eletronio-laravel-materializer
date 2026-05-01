<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePacienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'nome'            => 'required|string|max:255',
            'documento'       => ['required', 'string', Rule::unique('pacientes', 'documento')->ignore($id)],
            'data_nascimento' => 'required|date',
            'sexo'            => 'required|in:M,F,outro',
            'matricula'       => ['required', 'string', Rule::unique('pacientes', 'matricula')->ignore($id)],
            'curso'           => 'required|string|max:255',
            'contato'         => 'nullable|string|max:20',
            'endereco'        => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required'            => 'O nome é obrigatório.',
            'documento.required'       => 'O documento é obrigatório.',
            'documento.unique'         => 'Este documento já está cadastrado para outro paciente.',
            'data_nascimento.required' => 'A data de nascimento é obrigatória.',
            'data_nascimento.date'     => 'Informe uma data válida.',
            'sexo.required'            => 'O sexo é obrigatório.',
            'sexo.in'                  => 'O sexo deve ser M, F ou outro.',
            'matricula.required'       => 'A matrícula é obrigatória.',
            'matricula.unique'         => 'Esta matrícula já está cadastrada para outro paciente.',
            'curso.required'           => 'O curso é obrigatório.',
        ];
    }
}
