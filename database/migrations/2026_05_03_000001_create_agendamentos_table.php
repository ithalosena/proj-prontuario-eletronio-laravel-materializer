<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agendamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->foreignId('profissional_id')->constrained('profissionais')->cascadeOnDelete();
            // Quem criou e quem eventualmente cancelou o agendamento (ST-09: auditoria)
            $table->foreignId('criado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cancelado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('data_hora');
            // Tipo sem FK — mesma estratégia de consultas.tipo (valor livre validado no FormRequest)
            $table->string('tipo');
            $table->enum('status', ['pendente', 'confirmado', 'realizado', 'cancelado'])->default('pendente');
            $table->text('observacao')->nullable();
            $table->text('motivo_cancelamento')->nullable();
            $table->dateTime('cancelado_em')->nullable();
            // Consulta gerada ao realizar o agendamento (nullable até que seja realizado)
            $table->foreignId('consulta_id')->nullable()->constrained('consultas')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index('paciente_id');
            $table->index('profissional_id');
            $table->index(['data_hora', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agendamentos');
    }
};
