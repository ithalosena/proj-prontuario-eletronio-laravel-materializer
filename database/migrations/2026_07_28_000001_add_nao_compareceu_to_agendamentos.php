<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * DEC-2 (v0.11.1): adiciona o estado 'nao_compareceu' ao ciclo do agendamento (no-show).
 *
 * Distinto de 'cancelado' (desmarcado antes) — aqui o paciente FALTOU. A justificativa
 * reusa motivo_cancelamento/cancelado_em/cancelado_por_id (sem colunas novas).
 *
 * ENUM cross-database SEM doctrine/dbal:
 *  - MySQL: ALTER ... MODIFY para incluir o novo valor (preserva os dados existentes).
 *  - SQLite (testes): a coluna enum é varchar+CHECK e a tabela está VAZIA durante a migration
 *    (migrations rodam antes dos seeders). Recria a coluna como string simples (sem o CHECK),
 *    soltando/refazendo o índice composto (data_hora, status) que a envolve.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE agendamentos MODIFY COLUMN status ENUM('pendente','confirmado','realizado','cancelado','nao_compareceu') NOT NULL DEFAULT 'pendente'");
            return;
        }

        $this->recriarStatusComoString();
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE agendamentos MODIFY COLUMN status ENUM('pendente','confirmado','realizado','cancelado') NOT NULL DEFAULT 'pendente'");
            return;
        }

        $this->recriarStatusComoString();
    }

    // Recria a coluna status (string) em bancos que não suportam ALTER de enum (SQLite).
    // Seguro porque a tabela está vazia quando esta migration roda nos testes.
    private function recriarStatusComoString(): void
    {
        Schema::table('agendamentos', function (Blueprint $table) {
            $table->dropIndex(['data_hora', 'status']);
        });
        Schema::table('agendamentos', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        Schema::table('agendamentos', function (Blueprint $table) {
            $table->string('status')->default('pendente');
        });
        Schema::table('agendamentos', function (Blueprint $table) {
            $table->index(['data_hora', 'status']);
        });
    }
};
