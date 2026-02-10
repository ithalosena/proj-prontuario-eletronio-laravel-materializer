<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prescricao extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'prescricoes';

    protected $fillable = [
        'consulta_id',
        'nome_medicamento',
        'dosagem',
        'frequencia',
        'duracao',
        'observacao',
    ];

    public function consulta()
    {
        return $this->belongsTo(Consulta::class);
    }
}
