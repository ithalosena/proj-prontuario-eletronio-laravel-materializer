<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/*
 * Model: DisponibilidadeBloco
 *
 * Representa um bloco de horário de atendimento recorrente por dia da semana.
 * Múltiplos blocos por dia são permitidos (ex: 08–12 e 14–17 na mesma segunda-feira).
 * Substituiu a tabela `disponibilidades` que permitia apenas um bloco por dia.
 */
class DisponibilidadeBloco extends Model
{
    protected $table = 'disponibilidade_blocos';

    protected $fillable = [
        'profissional_id',
        'dia_semana',
        'hora_inicio',
        'hora_fim',
    ];

    public function profissional()
    {
        return $this->belongsTo(Profissional::class);
    }
}
