<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exame extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'consulta_id',
        'tipo',
        'observacao',
        'data_solicitacao',
        'data_resultado',
        'resultado',
    ];

    protected $casts = [
        'data_solicitacao' => 'date',
        'data_resultado' => 'date',
    ];

    public function consulta()
    {
        return $this->belongsTo(Consulta::class);
    }
}
