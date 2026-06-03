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
            // Dados cadastrais (sensíveis — UX-24; readonly para nivel > 2, mas ainda submetidos)
            'nome'            => 'required|string|max:255',
            'documento'       => ['required', 'string', Rule::unique('pacientes', 'documento')->ignore($id)],
            'data_nascimento' => 'required|date',
            'sexo'            => 'required|in:M,F,outro',
            'matricula'       => ['required', 'string', Rule::unique('pacientes', 'matricula')->ignore($id)],
            'curso'           => 'required|string|max:255',

            // Contatos (complementares — editáveis por nivel ≤ 4)
            'contato'              => 'nullable|string|max:20',
            'telefone_alternativo' => 'nullable|string|max:20',
            'email_alternativo'    => 'nullable|email|max:255',

            // Endereço — campo legado + estruturado (ST-15)
            'endereco'         => 'nullable|string|max:255',
            'cep'              => 'nullable|string|max:9',
            'logradouro'       => 'nullable|string|max:255',
            'numero'           => 'nullable|string|max:20',
            'complemento'      => 'nullable|string|max:100',
            'bairro'           => 'nullable|string|max:100',
            'cidade'           => 'nullable|string|max:100',
            'uf'               => 'nullable|string|size:2',
            'ponto_referencia' => 'nullable|string|max:255',

            // Dados complementares
            'nome_social'         => 'nullable|string|max:255',
            'naturalidade_cidade' => 'nullable|string|max:100',
            'naturalidade_uf'     => 'nullable|string|size:2',
            'raca_cor'            => ['nullable', Rule::in(['branca','preta','parda','amarela','indigena','nao_declarado'])],
            'estado_civil'        => ['nullable', Rule::in(['solteiro','casado','divorciado','viuvo','uniao_estavel'])],
            'nome_mae'            => 'nullable|string|max:255',

            // Contatos de emergência
            'contato_emergencia_nome'        => 'nullable|string|max:255',
            'contato_emergencia_telefone'    => 'nullable|string|max:20',
            'contato_emergencia_parentesco'  => 'nullable|string|max:50',
            'contato_emergencia2_nome'       => 'nullable|string|max:255',
            'contato_emergencia2_telefone'   => 'nullable|string|max:20',
            'contato_emergencia2_parentesco' => 'nullable|string|max:50',

            // Responsável legal (menores)
            'responsavel_nome'       => 'nullable|string|max:255',
            'responsavel_cpf'        => 'nullable|string|max:14',
            'responsavel_telefone'   => 'nullable|string|max:20',
            'responsavel_email'      => 'nullable|email|max:255',
            'responsavel_parentesco' => 'nullable|string|max:50',
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
