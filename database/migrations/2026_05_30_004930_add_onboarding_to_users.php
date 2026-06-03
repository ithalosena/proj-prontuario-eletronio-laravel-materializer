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
        Schema::table('users', function (Blueprint $table) {
            // ST-15: controle de wizard de primeiro acesso
            $table->boolean('onboarding_completo')->default(false)->after('email_verified_at');
            // tutorial_completo reservado para Shepherd.js (pós-TCC) — coluna incluída agora para evitar migration futura
            $table->boolean('tutorial_completo')->default(false)->after('onboarding_completo');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['onboarding_completo', 'tutorial_completo']);
        });
    }
};
