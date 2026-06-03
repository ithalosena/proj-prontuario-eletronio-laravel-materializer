<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OnboardingPacienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $paciente = auth()->user()->paciente;
        $isMenor  = $paciente && $paciente->data_nascimento->age < 18;

        return [
            // Passo 0 — campos editáveis (email, matrícula, curso são read-only, ignorados)
            'nome'          => ['required', 'string', 'max:255'],
            'documento'     => ['required', 'string', 'max:255'],
            'data_nascimento' => ['required', 'date'],
            'sexo'          => ['required', Rule::in(['M', 'F', 'outro'])],

            // Passo 1 — Endereço
            'cep'        => ['required', 'string', 'max:9'],
            'logradouro' => ['required', 'string', 'max:255'],
            'numero'     => ['required', 'string', 'max:20'],
            'complemento'     => ['nullable', 'string', 'max:100'],
            'bairro'     => ['required', 'string', 'max:100'],
            'cidade'     => ['required', 'string', 'max:100'],
            'uf'         => ['required', 'string', 'size:2'],
            'ponto_referencia' => ['nullable', 'string', 'max:255'],

            // Passo 2 — Contatos
            'contato'              => ['required', 'string', 'max:20'],
            'telefone_alternativo' => ['nullable', 'string', 'max:20'],
            'email_alternativo'    => ['nullable', 'email', 'max:255',
                // Não pode ser igual ao email principal do usuário
                function ($attr, $value, $fail) {
                    if ($value && $value === auth()->user()->email) {
                        $fail('O e-mail alternativo não pode ser igual ao e-mail principal.');
                    }
                },
            ],

            // Passo 3 — Dados complementares (todos opcionais)
            'nome_social'        => ['nullable', 'string', 'max:255'],
            'naturalidade_cidade'=> ['nullable', 'string', 'max:100'],
            'naturalidade_uf'    => ['nullable', 'string', 'size:2'],
            'raca_cor'           => ['nullable', Rule::in(['branca','preta','parda','amarela','indigena','nao_declarado'])],
            'estado_civil'       => ['nullable', Rule::in(['solteiro','casado','divorciado','viuvo','uniao_estavel'])],
            'nome_mae'           => ['nullable', 'string', 'max:255'],

            // Passo 4 — Emergência primário (obrigatório)
            'contato_emergencia_nome'       => ['required', 'string', 'max:255'],
            'contato_emergencia_telefone'   => ['required', 'string', 'max:20'],
            'contato_emergencia_parentesco' => ['required', 'string', 'max:50'],

            // Passo 4 — Emergência secundário (validação atômica: se qualquer campo, exige todos)
            'contato_emergencia2_nome'       => ['nullable', 'string', 'max:255',
                'required_with:contato_emergencia2_telefone,contato_emergencia2_parentesco',
            ],
            'contato_emergencia2_telefone'   => ['nullable', 'string', 'max:20',
                'required_with:contato_emergencia2_nome,contato_emergencia2_parentesco',
            ],
            'contato_emergencia2_parentesco' => ['nullable', 'string', 'max:50',
                'required_with:contato_emergencia2_nome,contato_emergencia2_telefone',
            ],

            // Passo 4 — Responsável legal (obrigatório se menor de 18)
            'responsavel_nome'       => [$isMenor ? 'required' : 'nullable', 'string', 'max:255'],
            'responsavel_cpf'        => [$isMenor ? 'required' : 'nullable', 'string', 'max:14'],
            'responsavel_telefone'   => [$isMenor ? 'required' : 'nullable', 'string', 'max:20'],
            'responsavel_email'      => [$isMenor ? 'required' : 'nullable', 'email', 'max:255'],
            'responsavel_parentesco' => [$isMenor ? 'required' : 'nullable', 'string', 'max:50'],

            // Passo 5 — Dados de saúde (todos opcionais)
            'tipo_sanguineo'   => ['nullable', Rule::in(['A+','A-','B+','B-','AB+','AB-','O+','O-','NS'])],
            'peso_kg'          => ['nullable', 'numeric', 'min:1', 'max:300'],
            'altura_cm'        => ['nullable', 'integer', 'min:30', 'max:250'],
            'alergias'                  => ['nullable', 'string'],
            'medicamentos_uso_continuo' => ['nullable', 'string'],
            'condicoes_cronicas'        => ['nullable', 'string'],
            'cirurgias_previas'         => ['nullable', 'string'],
            'tabagismo'        => ['nullable', Rule::in(['nao','ex_fumante','sim'])],
            'etilismo'         => ['nullable', Rule::in(['nao','ocasional','frequente'])],
            'atividade_fisica' => ['nullable', Rule::in(['sedentario','leve','moderada'])],
        ];
    }

    public function messages(): array
    {
        return [
            'contato_emergencia_nome.required'       => 'Informe o nome do contato de emergência.',
            'contato_emergencia_telefone.required'   => 'Informe o telefone do contato de emergência.',
            'contato_emergencia_parentesco.required' => 'Informe o parentesco do contato de emergência.',
            'responsavel_nome.required'       => 'Informe o nome do responsável legal.',
            'responsavel_cpf.required'        => 'Informe o CPF do responsável legal.',
            'responsavel_telefone.required'   => 'Informe o telefone do responsável legal.',
            'responsavel_email.required'      => 'Informe o e-mail do responsável legal.',
            'responsavel_parentesco.required' => 'Informe o parentesco do responsável legal.',
            'contato_emergencia2_nome.required_with'       => 'Se preencher o 2º contato, informe o nome.',
            'contato_emergencia2_telefone.required_with'   => 'Se preencher o 2º contato, informe o telefone.',
            'contato_emergencia2_parentesco.required_with' => 'Se preencher o 2º contato, informe o parentesco.',
        ];
    }
}
