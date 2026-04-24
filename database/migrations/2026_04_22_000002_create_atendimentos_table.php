<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atendimentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('restrict');
            $table->foreignId('profissional_id')->constrained('profissionais')->onDelete('restrict');
            $table->foreignId('criado_por_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('fechado_por_id')->nullable()->constrained('users')->onDelete('restrict');
            $table->enum('status', ['aberto', 'fechado'])->default('aberto');
            $table->timestamp('fechado_em')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('paciente_id');
            $table->index('profissional_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atendimentos');
    }
};
