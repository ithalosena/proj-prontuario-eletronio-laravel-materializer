<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Paciente extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'nome',
        'contato',
        'documento',
        'data_nascimento',
        'sexo',
        'endereco',
        'matricula',
        'curso',
    ];

    protected $casts = [
        'data_nascimento' => 'date',
    ];

    public function getIdadeAttribute(): int
    {
        return $this->data_nascimento->age;
    }

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
