<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 * Model: Exame
 *
 * Representa um exame solicitado durante uma consulta clínica.
 * Cada exame pertence a uma consulta e registra o tipo, observações,
 * data de solicitação e resultado quando disponível.
 *
 * 'criado_por_id' foi adicionado no ST-08 para rastrear autoria:
 * só o criador (ou admin) pode editar/deletar o registro.
 */
class Exame extends Model
{
    use HasFactory, SoftDeletes;

    /*
     * Campos permitidos para preenchimento em massa.
     * 'criado_por_id' é preenchido automaticamente no controller (Auth::id()).
     * As datas usam Carbon via $casts — isso permite ->format('d/m/Y') nas views.
     */
    protected $fillable = [
        'consulta_id',
        'criado_por_id', // ST-08: autoria do registro
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

    // =========================================================
    // Relacionamentos Eloquent
    // =========================================================

    // Consulta à qual este exame está vinculado
    public function consulta()
    {
        return $this->belongsTo(Consulta::class);
    }

    // Usuário que criou este exame (ST-08: controle de autoria)
    public function criadoPor()
    {
        return $this->belongsTo(User::class, 'criado_por_id');
    }
}
