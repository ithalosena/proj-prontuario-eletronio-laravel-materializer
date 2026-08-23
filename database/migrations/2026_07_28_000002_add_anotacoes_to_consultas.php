<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * E3d (v0.11.1, DEC-5): campo livre de anotações na consulta.
 *
 * Motivação: o SOAP (queixa/anamnese/diagnóstico/conduta) atende o modelo médico,
 * mas outros perfis (psicólogo, assistente social...) precisam de um espaço de
 * registro livre/complementar. Campo opcional, disponível para todos os perfis.
 * ADD COLUMN nullable é aditivo e cross-db (MySQL/SQLite) — sem doctrine/dbal.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultas', function (Blueprint $table) {
            $table->text('anotacoes')->nullable()->after('conduta');
        });
    }

    public function down(): void
    {
        Schema::table('consultas', function (Blueprint $table) {
            $table->dropColumn('anotacoes');
        });
    }
};
