<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disponibilidade_blocos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profissional_id')->constrained('profissionais')->cascadeOnDelete();
            // 0=Dom, 1=Seg, ..., 6=Sáb — sem UNIQUE(profissional, dia): múltiplos blocos por dia
            $table->unsignedTinyInteger('dia_semana');
            $table->time('hora_inicio');
            $table->time('hora_fim');
            $table->timestamps();

            $table->index(['profissional_id', 'dia_semana']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disponibilidade_blocos');
    }
};
