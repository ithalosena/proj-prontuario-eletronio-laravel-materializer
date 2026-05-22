<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Arq-05 (v0.7.6): índices em campos frequentemente buscados na tabela pacientes.
// documento e matricula são usados em buscas de autocomplete e listagem — sem índice,
// a query faz full table scan em tabelas com 55+ registros crescendo para milhares.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            // Índice simples (não unique) — documento pode ser CPF ou RG; duplicatas possíveis por design
            $table->index('documento', 'idx_pacientes_documento');
            // Matrícula é identificador institucional — buscas frequentes no autocomplete
            $table->index('matricula', 'idx_pacientes_matricula');
        });
    }

    public function down(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->dropIndex('idx_pacientes_documento');
            $table->dropIndex('idx_pacientes_matricula');
        });
    }
};
