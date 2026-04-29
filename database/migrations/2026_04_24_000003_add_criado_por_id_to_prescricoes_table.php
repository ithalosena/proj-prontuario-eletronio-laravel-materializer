<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * ST-08: Adiciona coluna criado_por_id na tabela prescricoes.
 *
 * Mesma lógica da migration de consultas:
 * - nullable() para não quebrar registros existentes
 * - nullOnDelete() para não perder a prescrição se o usuário for removido
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prescricoes', function (Blueprint $table) {
            $table->foreignId('criado_por_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->after('consulta_id');
        });
    }

    public function down(): void
    {
        Schema::table('prescricoes', function (Blueprint $table) {
            $table->dropForeign(['criado_por_id']);
            $table->dropColumn('criado_por_id');
        });
    }
};
