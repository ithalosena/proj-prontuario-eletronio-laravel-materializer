<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * ST-08: Adiciona coluna criado_por_id na tabela consultas.
 *
 * Essa coluna guarda o ID do usuário que criou o registro.
 * Ela é nullable porque registros antigos (antes desta migration) não
 * têm autor rastreado — não queremos quebrar o banco existente.
 *
 * nullOnDelete(): se o usuário for deletado, o campo vira NULL
 * em vez de apagar a consulta junto — protege o histórico clínico.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultas', function (Blueprint $table) {
            $table->foreignId('criado_por_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->after('atendimento_id');
        });
    }

    public function down(): void
    {
        Schema::table('consultas', function (Blueprint $table) {
            $table->dropForeign(['criado_por_id']);
            $table->dropColumn('criado_por_id');
        });
    }
};
