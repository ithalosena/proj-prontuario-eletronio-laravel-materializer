<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/*
 * Model: AgendaConfig
 *
 * Configurações de agenda por profissional.
 * Um registro por profissional (unique profissional_id).
 * Defaults aplicados pelo controller quando não existe registro.
 */
class AgendaConfig extends Model
{
    protected $table = 'agenda_config';

    protected $fillable = [
        'profissional_id',
        'duracao_minutos',
        'buffer_minutos',
        'antecedencia_minima_horas',
        'antecedencia_maxima_dias',
        'aceitar_agendamentos_online',
        'reservar_horarios_encaixe',
        'turno_manha_inicio',
        'turno_manha_fim',
        'turno_tarde_inicio',
        'turno_tarde_fim',
        'turno_noite_inicio',
        'turno_noite_fim',
    ];

    protected $casts = [
        'duracao_minutos'            => 'integer',
        'buffer_minutos'             => 'integer',
        'antecedencia_minima_horas'  => 'integer',
        'antecedencia_maxima_dias'   => 'integer',
        'aceitar_agendamentos_online' => 'boolean',
        'reservar_horarios_encaixe'  => 'boolean',
    ];

    public function profissional()
    {
        return $this->belongsTo(Profissional::class);
    }
}
