<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * DT-MOD-01 (Modelo A): liga o atendimento ao agendamento que o originou.
 *
 * A FK é nullable de propósito — atendimento SEM agendamento_id é a demanda
 * espontânea (encaixe/emergência, padrão SUS); COM agendamento_id é a demanda
 * agendada. O ENUM de status (aberto/fechado) NÃO muda: como o atendimento só
 * é criado ao salvar a 1ª consulta (nunca fica vazio), o estado 'agendado'
 * é desnecessário.
 *
 * nullOnDelete: se o agendamento for apagado, o atendimento vira espontâneo
 * em vez de ser bloqueado/apagado — o registro clínico é preservado.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('atendimentos', function (Blueprint $table) {
            $table->foreignId('agendamento_id')
                ->nullable()
                ->after('profissional_id')
                ->constrained('agendamentos')
                ->nullOnDelete();

            $table->index('agendamento_id');
        });
    }

    public function down(): void
    {
        Schema::table('atendimentos', function (Blueprint $table) {
            $table->dropForeign(['agendamento_id']);
            $table->dropColumn('agendamento_id');
        });
    }
};
