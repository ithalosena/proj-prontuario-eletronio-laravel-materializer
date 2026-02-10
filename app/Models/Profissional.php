<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Profissional extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'profissionais';

    protected $fillable = [
        'user_id',
        'nome',
        'contato',
        'especialidade',
        'registro_profissional',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function consultas()
    {
        return $this->hasMany(Consulta::class);
    }

    public function exames()
    {
        return $this->hasManyThrough(Exame::class, Consulta::class);
    }

    public function prescricoes()
    {
        return $this->hasManyThrough(Prescricao::class, Consulta::class);
    }
}
