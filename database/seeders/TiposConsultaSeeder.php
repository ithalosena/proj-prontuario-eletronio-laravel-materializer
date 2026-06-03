<?php

namespace Database\Seeders;

use App\Models\TipoConsulta;
use Illuminate\Database\Seeder;

// Popula a tabela de tipos de consulta com os valores canônicos do sistema.
// Os 6 primeiros espelham as especialidades; "Retorno" e os genéricos (ANALISE-03, v0.10.1)
// representam o MOTIVO do encontro, ortogonal à especialidade.
class TiposConsultaSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            ['nome' => 'Clínico Geral',   'ordem' => 1],
            ['nome' => 'Odontologia',     'ordem' => 2],
            ['nome' => 'Psicologia',      'ordem' => 3],
            ['nome' => 'Nutrição',        'ordem' => 4],
            ['nome' => 'Fisioterapia',    'ordem' => 5],
            ['nome' => 'Serviço Social',  'ordem' => 6],
            ['nome' => 'Retorno',         'ordem' => 7],
            // ANALISE-03 (v0.10.1): tipos genéricos de motivo de consulta
            ['nome' => 'Primeira Consulta','ordem' => 8],
            ['nome' => 'Urgência',        'ordem' => 9],
            ['nome' => 'Avaliação',       'ordem' => 10],
        ];

        foreach ($tipos as $dados) {
            TipoConsulta::firstOrCreate(
                ['nome' => $dados['nome']],
                ['ativo' => true, 'ordem' => $dados['ordem']]
            );
        }

        $this->command->info('✓ TiposConsultaSeeder: ' . TipoConsulta::count() . ' tipos de consulta.');
    }
}
