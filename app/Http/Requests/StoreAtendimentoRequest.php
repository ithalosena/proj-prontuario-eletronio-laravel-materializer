<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/*
 * FormRequest: StoreAtendimentoRequest
 *
 * Valida os dados enviados pelo formulário de abertura de atendimento.
 * O Laravel chama esta classe automaticamente antes do método store() do controller.
 * Se a validação falhar, o usuário é redirecionado de volta com os erros preenchidos,
 * sem precisar escrever nenhuma lógica de validação no controller.
 */
class StoreAtendimentoRequest extends FormRequest
{
    /*
     * Autoriza todos os usuários autenticados a enviar este formulário.
     * O controle de quem pode acessar a rota é feito pelo middleware CheckNivel,
     * então aqui retornamos true sem precisar verificar novamente.
     */
    public function authorize(): bool
    {
        return true;
    }

    /*
     * Regras de validação dos campos do formulário.
     * 'required' garante que o campo não chegue vazio.
     * 'exists:tabela,coluna' verifica se o ID informado realmente existe no banco,
     * evitando vincular um atendimento a um paciente ou profissional inexistente.
     */
    public function rules(): array
    {
        return [
            'paciente_id'     => ['required', 'exists:pacientes,id'],
            'profissional_id' => ['required', 'exists:profissionais,id'],
        ];
    }

    // Mensagens de erro em português para exibição no formulário
    public function messages(): array
    {
        return [
            'paciente_id.required'     => 'Selecione um paciente.',
            'paciente_id.exists'       => 'Paciente inválido.',
            'profissional_id.required' => 'Selecione um profissional.',
            'profissional_id.exists'   => 'Profissional inválido.',
        ];
    }
}
