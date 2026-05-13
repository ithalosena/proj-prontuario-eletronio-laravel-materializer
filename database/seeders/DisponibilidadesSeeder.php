<?php

namespace Database\Seeders;

use App\Models\AgendaConfig;
use App\Models\DisponibilidadeBloco;
use App\Models\Profissional;
use Illuminate\Database\Seeder;

/*
 * Cria disponibilidades padrão para todos os profissionais (novo schema: disponibilidade_blocos).
 * Seg-Sex: 08:00-17:00. Sábado: 08:00-12:00. Domingo: sem bloco (inativo).
 * Também cria AgendaConfig com valores padrão para cada profissional.
 */
class DisponibilidadesSeeder extends Seeder
{
    public function run(): void
    {
        $profissionais = Profissional::all();

        foreach ($profissionais as $profissional) {
            // Segunda a Sexta (1–5) — horário comercial
            foreach (range(1, 5) as $dia) {
                DisponibilidadeBloco::firstOrCreate(
                    [
                        'profissional_id' => $profissional->id,
                        'dia_semana'      => $dia,
                        'hora_inicio'     => '08:00:00',
                        'hora_fim'        => '17:00:00',
                    ]
                );
            }

            // Sábado (6) — meio período
            DisponibilidadeBloco::firstOrCreate(
                [
                    'profissional_id' => $profissional->id,
                    'dia_semana'      => 6,
                    'hora_inicio'     => '08:00:00',
                    'hora_fim'        => '12:00:00',
                ]
            );

            // Configuração padrão de agenda
            AgendaConfig::firstOrCreate(
                ['profissional_id' => $profissional->id],
                [
                    'duracao_minutos'           => 30,
                    'buffer_minutos'            => 0,
                    'antecedencia_minima_horas' => 1,
                    'antecedencia_maxima_dias'  => 60,
                ]
            );
        }

        $totalBlocos  = DisponibilidadeBloco::count();
        $totalConfigs = AgendaConfig::count();
        $this->command->info("✓ DisponibilidadesSeeder: {$totalBlocos} blocos + {$totalConfigs} configs ({$profissionais->count()} profissionais).");
    }
}
