<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Especialidade extends Model
{
    use HasFactory;

    protected $fillable = ['nome', 'ativo', 'ordem'];

    protected $casts = ['ativo' => 'boolean'];

    // Retorna apenas especialidades ativas
    public function scopeAtivo($query)
    {
        return $query->where('ativo', true);
    }

    // Ordena pela coluna 'ordem', depois pelo nome
    public function scopeOrdenado($query)
    {
        return $query->orderBy('ordem')->orderBy('nome');
    }
}
