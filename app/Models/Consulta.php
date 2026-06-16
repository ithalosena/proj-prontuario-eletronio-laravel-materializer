<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 * Model: Consulta
 *
 * Representa uma consulta clínica registrada dentro de um atendimento.
 * Cada consulta segue o formato SOAP (Queixa, Anamnese, Diagnóstico, Conduta)
 * e pode ter vários exames e prescrições vinculados a ela.
 *
 * A FK 'atendimento_id' foi adicionada na v0.3.3 para vincular cada consulta
 * ao seu atendimento pai. Registros antigos podem ter esse campo nulo.
 */
class Consulta extends Model
{
    use HasFactory, SoftDeletes;

    /*
     * Campos permitidos para preenchimento em massa.
     * Incluímos 'atendimento_id' para vincular a consulta ao atendimento pai.
     * Todos os campos do SOAP (queixa, anamnese, diagnostico, conduta) estão aqui
     * porque o formulário os envia de uma vez só.
     */
    protected $fillable = [
        'atendimento_id',
        'criado_por_id', // ST-08: quem criou o registro (só o autor pode editar/deletar)
        'profissional_id',
        'paciente_id',
        'data_hora',
        'tipo',
        'queixa',
        'anamnese',
        'diagnostico',
        'conduta',
    ];

    /*
     * Converte 'data_hora' para Carbon automaticamente.
     * Assim podemos usar $consulta->data_hora->format('d/m/Y H:i') nas views
     * sem precisar converter manualmente.
     */
    protected $casts = [
        'data_hora' => 'datetime',
    ];

    // =========================================================
    // Relacionamentos Eloquent
    // =========================================================

    // Usuário que criou este registro (ST-08: controle de autoria)
    public function criadoPor()
    {
        return $this->belongsTo(User::class, 'criado_por_id');
    }

    // Uma consulta pertence a um atendimento (agrupador de consultas do paciente)
    public function atendimento()
    {
        return $this->belongsTo(Atendimento::class);
    }

    // Profissional que realizou a consulta
    public function profissional()
    {
        return $this->belongsTo(Profissional::class);
    }

    // Paciente atendido na consulta
    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    // Exames solicitados durante esta consulta
    public function exames()
    {
        return $this->hasMany(Exame::class);
    }

    // Medicamentos prescritos durante esta consulta
    public function prescricoes()
    {
        return $this->hasMany(Prescricao::class);
    }

    // =========================================================
    // Scopes de consulta
    // =========================================================

    /*
     * UX-P09 (v0.10.2): consultas às quais o usuário PODE anexar exame/prescrição.
     * - Nível 3 (profissional) vê apenas as próprias consultas (respeita a ConsultaPolicy::view).
     * - Nunca consultas de atendimento FECHADO (princípio read-only do encerramento).
     * - Consultas órfãs (sem atendimento) seguem visíveis — tratadas no débito DT-MOD-01.
     */
    public function scopeAnexaveisPor($query, User $user)
    {
        return $query
            ->when(
                $user->nivelAcesso() === 3 && $user->profissional,
                fn($q) => $q->where('profissional_id', $user->profissional->id)
            )
            ->whereDoesntHave('atendimento', fn($q) => $q->where('status', 'fechado'));
    }
}
