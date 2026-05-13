<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agenda_config', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profissional_id')->unique()->constrained('profissionais')->cascadeOnDelete();
            $table->smallInteger('duracao_minutos')->default(30);          // 15/30/45/60
            $table->smallInteger('buffer_minutos')->default(0);            // 0/5/10/15
            $table->smallInteger('antecedencia_minima_horas')->default(1); // 1/2/4/8/24/48
            $table->smallInteger('antecedencia_maxima_dias')->default(60); // 30/60/90
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agenda_config');
    }
};
