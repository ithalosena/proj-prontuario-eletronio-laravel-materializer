<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Paciente extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'nome', 'nome_social',
        'contato', 'telefone_alternativo', 'email_alternativo',
        'documento', 'data_nascimento', 'sexo',
        // Endereço — campo legado mantido; campos estruturados adicionados no ST-15
        'endereco',
        'cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf', 'ponto_referencia',
        // Dados complementares
        'naturalidade_cidade', 'naturalidade_uf', 'raca_cor', 'estado_civil', 'nome_mae',
        // Contatos de emergência
        'contato_emergencia_nome', 'contato_emergencia_telefone', 'contato_emergencia_parentesco',
        'contato_emergencia2_nome', 'contato_emergencia2_telefone', 'contato_emergencia2_parentesco',
        // Responsável legal (obrigatório para menores de 18).
        // responsavel_cpf: coluna preservada no banco porém SEM USO desde v0.10.5 — removida do fillable
        // para não ser mais mass-assignável (nenhum formulário/fluxo envia esse campo).
        'responsavel_nome', 'responsavel_telefone', 'responsavel_email', 'responsavel_parentesco',
        // Dados clínicos autorreferidos
        'tipo_sanguineo', 'peso_kg', 'altura_cm',
        'alergias', 'medicamentos_uso_continuo', 'condicoes_cronicas', 'cirurgias_previas',
        'tabagismo', 'etilismo', 'atividade_fisica',
        'matricula', 'curso',
    ];

    protected $casts = [
        'data_nascimento' => 'date',
    ];

    public function getIdadeAttribute(): int
    {
        return $this->data_nascimento->age;
    }

    /*
     * v0.10.3+: Nome de EXIBIÇÃO preferido do paciente — nome social se houver, senão o de registro.
     * Usado nas telas do próprio paciente (perfil, dashboard).
     */
    public function getNomeExibicaoAttribute(): string
    {
        return trim($this->nome_social ?? '') ?: $this->nome;
    }

    /*
     * v0.10.3+: Nome para o PROFISSIONAL — formato "Social (Registro)" quando há nome social,
     * ex.: "Maria (Maria Fernanda Costa)". Sem nome social, só o de registro.
     */
    public function getNomeProfissionalAttribute(): string
    {
        $social = trim($this->nome_social ?? '');
        return $social !== '' ? "{$social} ({$this->nome})" : $this->nome;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function consultas()
    {
        return $this->hasMany(Consulta::class);
    }

    public function atendimentos()
    {
        return $this->hasMany(\App\Models\Atendimento::class);
    }

    // Último atendimento via subquery eficiente (sem N+1) — Laravel 8+ latestOfMany()
    public function ultimoAtendimento()
    {
        return $this->hasOne(\App\Models\Atendimento::class)->latestOfMany();
    }

    public function exames()
    {
        return $this->hasManyThrough(Exame::class, Consulta::class);
    }

    public function prescricoes()
    {
        return $this->hasManyThrough(Prescricao::class, Consulta::class);
    }

    public function agendamentos()
    {
        return $this->hasMany(\App\Models\Agendamento::class);
    }

    // Consentimentos LGPD do paciente (via user_id do usuário vinculado)
    public function consentimentos()
    {
        return $this->hasMany(Consentimento::class, 'user_id', 'user_id');
    }
}
