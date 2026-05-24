<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// L-01 (LGPD Art. 11, I): armazena o aceite explícito do paciente ao termo de consentimento.
// Registra qual versão do termo foi aceita e as evidências de aceite (IP + timestamp).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consentimentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // Versão do termo — permite detectar novos aceites quando a política mudar
            $table->string('versao_termo')->default('1.0');
            $table->timestamp('aceito_em')->useCurrent();
            // Evidências de aceite exigidas pelo Art. 5º, XII (dado de consentimento)
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consentimentos');
    }
};
