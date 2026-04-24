<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultas', function (Blueprint $table) {
            $table->foreignId('atendimento_id')
                ->nullable()
                ->after('id')
                ->constrained('atendimentos')
                ->onDelete('restrict');

            $table->index('atendimento_id');
        });
    }

    public function down(): void
    {
        Schema::table('consultas', function (Blueprint $table) {
            $table->dropForeign(['atendimento_id']);
            $table->dropColumn('atendimento_id');
        });
    }
};
