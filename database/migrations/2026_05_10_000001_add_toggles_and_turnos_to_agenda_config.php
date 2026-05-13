<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agenda_config', function (Blueprint $table) {
            // Toggles de comportamento online
            $table->boolean('aceitar_agendamentos_online')->default(true)->after('antecedencia_maxima_dias');
            $table->boolean('reservar_horarios_encaixe')->default(false)->after('aceitar_agendamentos_online');

            // Horários padrão dos turnos — usados como pré-preenchimento ao ativar célula
            $table->time('turno_manha_inicio')->default('08:00')->after('reservar_horarios_encaixe');
            $table->time('turno_manha_fim')->default('12:00')->after('turno_manha_inicio');
            $table->time('turno_tarde_inicio')->default('14:00')->after('turno_manha_fim');
            $table->time('turno_tarde_fim')->default('18:00')->after('turno_tarde_inicio');
            $table->time('turno_noite_inicio')->default('19:00')->after('turno_tarde_fim');
            $table->time('turno_noite_fim')->default('22:00')->after('turno_noite_inicio');
        });
    }

    public function down(): void
    {
        Schema::table('agenda_config', function (Blueprint $table) {
            $table->dropColumn([
                'aceitar_agendamentos_online',
                'reservar_horarios_encaixe',
                'turno_manha_inicio',
                'turno_manha_fim',
                'turno_tarde_inicio',
                'turno_tarde_fim',
                'turno_noite_inicio',
                'turno_noite_fim',
            ]);
        });
    }
};
