<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 * Model: Atendimento
 *
 * Representa uma "ficha de atendimento" aberta para um paciente.
 * Um atendimento agrupa uma ou mais consultas realizadas pelo mesmo profissional.
 * O fluxo é: abrir atendimento → registrar consultas → encerrar atendimento.
 * Enquanto aberto, novas consultas podem ser adicionadas e editadas.
 *
 * Usamos SoftDeletes para que registros excluídos não sumam do banco de verdade,
 * apenas fiquem marcados com deleted_at — importante para auditoria e histórico.
 */
class Atendimento extends Model
{
    use HasFactory, SoftDeletes;

    /*
     * Lista de campos que podem ser preenchidos via formulário (mass assignment).
     * O Laravel bloqueia qualquer campo que não esteja aqui por segurança,
     * evitando que um usuário mal-intencionado injete dados não esperados
     * através de campos ocultos no formulário.
     */
    protected $fillable = [
        'paciente_id',
        'profissional_id',
        'criado_por_id',   // quem abriu o atendimento (pode ser recepcionista ou o próprio profissional)
        'fechado_por_id',  // quem encerrou o atendimento
        'status',          // 'aberto' ou 'fechado'
        'fechado_em',      // data/hora do encerramento
    ];

    /*
     * Diz ao Laravel para converter o campo 'fechado_em' automaticamente
     * para um objeto Carbon (classe de datas do PHP/Laravel).
     * Isso permite usar métodos como ->format('d/m/Y') diretamente nas views,
     * sem precisar converter manualmente com strtotime() ou new DateTime().
     */
    protected $casts = [
        'fechado_em' => 'datetime',
    ];

    // =========================================================
    // Relacionamentos Eloquent
    // Cada método abaixo conecta este model com outro da aplicação.
    // O Laravel resolve os JOINs automaticamente usando as foreign keys
    // definidas na migration (ex: paciente_id → tabela pacientes).
    // =========================================================

    // Um atendimento pertence a um único paciente
    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    // Um atendimento pertence a um único profissional responsável
    public function profissional()
    {
        return $this->belongsTo(Profissional::class);
    }

    /*
     * Quem criou o atendimento é um User (não necessariamente o profissional).
     * Por isso usamos a FK explícita 'criado_por_id' em vez do padrão 'user_id'.
     * Sem isso, o Laravel tentaria buscar por 'user_id' e não encontraria a coluna.
     */
    public function criadoPor()
    {
        return $this->belongsTo(User::class, 'criado_por_id');
    }

    // Usuário que encerrou o atendimento — preenchido apenas quando status = 'fechado'
    public function fechadoPor()
    {
        return $this->belongsTo(User::class, 'fechado_por_id');
    }

    // Um atendimento pode ter várias consultas vinculadas
    public function consultas()
    {
        return $this->hasMany(Consulta::class);
    }

    // =========================================================
    // Métodos auxiliares
    // =========================================================

    /*
     * Atalho para verificar se o atendimento ainda está ativo.
     * Usado nas views e controllers para liberar ou bloquear ações,
     * por exemplo: só permite nova consulta se o atendimento estiver aberto.
     * Retorna true se status === 'aberto', false se 'fechado'.
     */
    public function isAberto(): bool
    {
        return $this->status === 'aberto';
    }
}
