<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disponibilidade_excecoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profissional_id')->constrained('profissionais')->cascadeOnDelete();
            $table->date('data_inicio');
            $table->date('data_fim');                      // igual a data_inicio para dia único
            $table->time('hora_inicio')->nullable();       // null = dia inteiro
            $table->time('hora_fim')->nullable();          // null = dia inteiro
            $table->enum('tipo', ['bloqueio', 'disponivel_extra']);
            $table->string('motivo', 255)->nullable();
            $table->timestamps();

            $table->index(['profissional_id', 'data_inicio', 'data_fim'], 'disp_excecoes_prof_datas_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disponibilidade_excecoes');
    }
};
