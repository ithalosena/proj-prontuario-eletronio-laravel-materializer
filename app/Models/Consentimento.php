<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consentimento extends Model
{
    // Tabela sem updated_at — consentimentos são imutáveis após o aceite
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'versao_termo',
        'aceito_em',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'aceito_em' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
