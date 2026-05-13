<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/*
 * Model: DisponibilidadeExcecao
 *
 * Exceções pontuais na disponibilidade de um profissional.
 * tipo='bloqueio'         → cancela disponibilidade no período (ex: férias, feriado).
 * tipo='disponivel_extra' → adiciona disponibilidade fora do padrão semanal.
 * hora_inicio/hora_fim nulos = exceção cobre o dia inteiro.
 */
class DisponibilidadeExcecao extends Model
{
    protected $table = 'disponibilidade_excecoes';

    protected $fillable = [
        'profissional_id',
        'data_inicio',
        'data_fim',
        'hora_inicio',
        'hora_fim',
        'tipo',
        'motivo',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim'    => 'date',
    ];

    public function profissional()
    {
        return $this->belongsTo(Profissional::class);
    }

    public function isBloqueio(): bool
    {
        return $this->tipo === 'bloqueio';
    }

    public function isDiaInteiro(): bool
    {
        return $this->hora_inicio === null;
    }
}
