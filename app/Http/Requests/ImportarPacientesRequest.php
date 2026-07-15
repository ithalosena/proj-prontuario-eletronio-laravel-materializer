<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportarPacientesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'arquivo' => 'required|file|mimes:csv,txt|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'arquivo.required' => 'Selecione um arquivo CSV para importar.',
            'arquivo.file'     => 'O envio deve ser um arquivo.',
            'arquivo.mimes'    => 'O arquivo deve ser um CSV (.csv ou .txt).',
            'arquivo.max'      => 'O arquivo deve ter no máximo 2MB.',
        ];
    }
}
