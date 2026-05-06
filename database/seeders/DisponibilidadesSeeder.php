<?php

namespace Database\Seeders;

use App\Models\Disponibilidade;
use App\Models\Profissional;
use Illuminate\Database\Seeder;

// Cria disponibilidades padrão para todos os profissionais cadastrados.
// Seg-Sex: 08:00-17:00 (ativo). Sábado: 08:00-12:00 (ativo). Dom: inativo.
// Necessário para que AgendamentoController::slots() retorne horários livres.
class DisponibilidadesSeeder extends Seeder
{
    public function run(): void
    {
        $profissionais = Profissional::all();

        foreach ($profissionais as $profissional) {
            // Domingo (0) — inativo
            Disponibilidade::firstOrCreate(
                ['profissional_id' => $profissional->id, 'dia_semana' => 0],
                ['hora_inicio' => '08:00', 'hora_fim' => '12:00', 'ativo' => false]
            );

            // Segunda a Sexta (1–5) — horário comercial
            foreach (range(1, 5) as $dia) {
                Disponibilidade::firstOrCreate(
                    ['profissional_id' => $profissional->id, 'dia_semana' => $dia],
                    ['hora_inicio' => '08:00', 'hora_fim' => '17:00', 'ativo' => true]
                );
            }

            // Sábado (6) — meio período
            Disponibilidade::firstOrCreate(
                ['profissional_id' => $profissional->id, 'dia_semana' => 6],
                ['hora_inicio' => '08:00', 'hora_fim' => '12:00', 'ativo' => true]
            );
        }

        $total = Disponibilidade::count();
        $this->command->info("✓ DisponibilidadesSeeder: {$total} registros ({$profissionais->count()} profissionais × 7 dias).");
    }
}
