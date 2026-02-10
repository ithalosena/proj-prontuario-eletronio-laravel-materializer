<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('consultas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profissional_id')->constrained('profissionais')->onDelete('restrict');
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('restrict');
            $table->dateTime('data_hora');
            $table->string('tipo')->nullable();
            $table->text('queixa')->nullable();
            $table->text('anamnese')->nullable();
            $table->text('diagnostico')->nullable();
            $table->text('conduta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('profissional_id');
            $table->index('paciente_id');
            $table->index('data_hora');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultas');
    }
};
