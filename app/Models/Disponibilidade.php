<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/*
 * Model: Disponibilidade
 *
 * Define os horários de atendimento de um profissional por dia da semana.
 * Unique(profissional_id, dia_semana) — um registro por dia por profissional.
 * Usado pelo AgendamentoController::slots() para gerar horários livres.
 */
class Disponibilidade extends Model
{
    use HasFactory;

    protected $fillable = [
        'profissional_id',
        'dia_semana',
        'hora_inicio',
        'hora_fim',
        'ativo',
    ];

    public function profissional()
    {
        return $this->belongsTo(Profissional::class);
    }
}
