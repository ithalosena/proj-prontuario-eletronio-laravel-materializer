<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 * Model: Agendamento
 *
 * Representa um agendamento clínico com ciclo de vida:
 * pendente → confirmado → realizado (vincula consulta_id) | cancelado
 *
 * Profissional e paciente podem criar agendamentos. Ao ser realizado,
 * o agendamento é vinculado à consulta gerada via consulta_id.
 */
class Agendamento extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'paciente_id',
        'profissional_id',
        'criado_por_id',
        'cancelado_por_id',
        'data_hora',
        'tipo',
        'status',
        'observacao',
        'motivo_cancelamento',
        'cancelado_em',
        'consulta_id',
    ];

    protected $casts = [
        'data_hora'    => 'datetime',
        'cancelado_em' => 'datetime',
    ];

    // =========================================================
    // Relacionamentos
    // =========================================================

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function profissional()
    {
        return $this->belongsTo(Profissional::class);
    }

    public function criadoPor()
    {
        return $this->belongsTo(User::class, 'criado_por_id');
    }

    public function canceladoPor()
    {
        return $this->belongsTo(User::class, 'cancelado_por_id');
    }

    public function consulta()
    {
        return $this->belongsTo(Consulta::class);
    }

    // =========================================================
    // Helpers de estado
    // =========================================================

    public function isPendente(): bool
    {
        return $this->status === 'pendente';
    }

    public function isConfirmado(): bool
    {
        return $this->status === 'confirmado';
    }

    public function isRealizado(): bool
    {
        return $this->status === 'realizado';
    }

    public function isCancelado(): bool
    {
        return $this->status === 'cancelado';
    }
}
