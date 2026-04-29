<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 * Model: Prescricao
 *
 * Representa um medicamento prescrito durante uma consulta clínica.
 * Cada prescrição pertence a uma consulta e registra nome do medicamento,
 * dosagem, frequência, duração e observações adicionais.
 *
 * 'criado_por_id' foi adicionado no ST-08 para rastrear autoria:
 * só o criador (ou admin) pode editar/deletar o registro.
 */
class Prescricao extends Model
{
    use HasFactory, SoftDeletes;

    /*
     * O Laravel por padrão tentaria a tabela 'prescricaos' (plural automático errado).
     * Declaramos explicitamente 'prescricoes' para bater com o nome real no banco.
     */
    protected $table = 'prescricoes';

    /*
     * Campos permitidos para preenchimento em massa.
     * 'criado_por_id' é preenchido automaticamente no controller (Auth::id()).
     */
    protected $fillable = [
        'consulta_id',
        'criado_por_id', // ST-08: autoria do registro
        'nome_medicamento',
        'dosagem',
        'frequencia',
        'duracao',
        'observacao',
    ];

    // =========================================================
    // Relacionamentos Eloquent
    // =========================================================

    // Consulta à qual esta prescrição está vinculada
    public function consulta()
    {
        return $this->belongsTo(Consulta::class);
    }

    // Usuário que criou esta prescrição (ST-08: controle de autoria)
    public function criadoPor()
    {
        return $this->belongsTo(User::class, 'criado_por_id');
    }
}
