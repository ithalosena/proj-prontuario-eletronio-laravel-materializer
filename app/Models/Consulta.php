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
}
