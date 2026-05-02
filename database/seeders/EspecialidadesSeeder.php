<?php

namespace Database\Seeders;

use App\Models\Especialidade;
use Illuminate\Database\Seeder;

// Popula a tabela de especialidades com os valores canônicos do sistema.
// Estes nomes são a fonte de verdade para validação nos FormRequests.
class EspecialidadesSeeder extends Seeder
{
    public function run(): void
    {
        $especialidades = [
            ['nome' => 'Clínico Geral',  'ordem' => 1],
            ['nome' => 'Odontologia',    'ordem' => 2],
            ['nome' => 'Psicologia',     'ordem' => 3],
            ['nome' => 'Nutrição',       'ordem' => 4],
            ['nome' => 'Fisioterapia',   'ordem' => 5],
            ['nome' => 'Serviço Social', 'ordem' => 6],
        ];

        foreach ($especialidades as $dados) {
            Especialidade::firstOrCreate(
                ['nome' => $dados['nome']],
                ['ativo' => true, 'ordem' => $dados['ordem']]
            );
        }

        $this->command->info('✓ EspecialidadesSeeder: ' . Especialidade::count() . ' especialidades.');
    }
}
