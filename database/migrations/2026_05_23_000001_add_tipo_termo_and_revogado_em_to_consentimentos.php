<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// v0.8.2 — expande consentimentos para cobrir titulares e operadores (Art. 47 LGPD)
// tipo_termo: distingue paciente/titular (Art. 11 I) de profissional/operador (Art. 47)
// revogado_em: suporte ao direito de revogação a qualquer momento (Art. 8º §5º)
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consentimentos', function (Blueprint $table) {
            // DEFAULT 'titular' garante que registros existentes da v0.8.1 permanecem válidos
            $table->enum('tipo_termo', ['titular', 'operador'])->default('titular')->after('versao_termo');

            // NULL indica consentimento ativo; preenchido quando o titular revoga (Art. 8º §5º)
            $table->timestamp('revogado_em')->nullable()->after('user_agent');
        });
    }

    public function down(): void
    {
        Schema::table('consentimentos', function (Blueprint $table) {
            $table->dropColumn(['tipo_termo', 'revogado_em']);
        });
    }
};
