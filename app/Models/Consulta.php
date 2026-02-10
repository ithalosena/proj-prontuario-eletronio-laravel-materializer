<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Consulta extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'profissional_id',
        'paciente_id',
        'data_hora',
        'tipo',
        'queixa',
        'anamnese',
        'diagnostico',
        'conduta',
    ];

    protected $casts = [
        'data_hora' => 'datetime',
    ];

    public function profissional()
    {
        return $this->belongsTo(Profissional::class);
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function exames()
    {
        return $this->hasMany(Exame::class);
    }

    public function prescricoes()
    {
        return $this->hasMany(Prescricao::class);
    }
}
