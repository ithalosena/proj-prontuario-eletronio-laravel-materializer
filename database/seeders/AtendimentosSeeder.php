<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\Profissional;
use App\Models\Paciente;
use App\Models\Atendimento;

// Cria ~100 atendimentos distribuídos em cenários específicos exigidos pelos roteiros de teste.
//
// Cenários cobertos:
//   Âncoras:          Maria (1 aberto), João (1 fechado), Lucas (1 fechado sem aberto — B.3.1)
//   Histórico longo:  Fernanda (9 atend.) e Gabriel (7 atend.) — bloco B.10.7
//   Mix aberto+fech:  Isabela, Mateus, Larissa — bloco B.10.4
//   Só abertos:       5 pacientes, 1 atend. aberto cada
//   Só fechados:      5 pacientes, 2-3 atend. fechados cada
//   Multi-esp:        Paulo (Clínico Geral + Psicologia), Sabrina (Odonto + Nutrição)
//   Sem atend.:       5 pacientes sem nenhum atendimento (já garantidos pela ausência aqui)
//   Variados:         demais pacientes com 1-3 atendimentos distribuídos por especialidade
class AtendimentosSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Carrega todos os profissionais indexados por e-mail para acesso rápido
        $profissionais = Profissional::with('user')->get()->keyBy(fn($p) => $p->user->email);

        $drSilva   = $profissionais['dr.silva@ifnmg.edu.br'];
        $draAna    = $profissionais['dra.ana@ifnmg.edu.br'];
        $drPedro   = $profissionais['dr.pedro@ifnmg.edu.br'];
        $drMendes  = $profissionais['dr.mendes@ifnmg.edu.br'];
        $draPaula  = $profissionais['dra.paula@ifnmg.edu.br'];
        $draCamila = $profissionais['dra.camila@ifnmg.edu.br'];
        $drBruno   = $profissionais['dr.bruno@ifnmg.edu.br'];
        $draJuliana= $profissionais['dra.juliana@ifnmg.edu.br'];

        // Lista de todos os profissionais para distribuição nos atendimentos variados
        $todosProfissionais = [$drSilva, $draAna, $drPedro, $drMendes, $draPaula, $draCamila, $drBruno, $draJuliana];

        // Carrega pacientes indexados por matrícula
        $pacientes = Paciente::all()->keyBy('matricula');

        // =====================================================================
        // ÂNCORAS — atendimentos documentados nos roteiros de teste
        // =====================================================================

        // Maria Fernanda Costa: 1 ABERTO com Dr. Silva (pré-requisito de vários testes do roteiro B)
        $this->abrir($pacientes['2023001'], $drSilva);

        // João Victor Almeida: 1 FECHADO com Dra. Ana (âncora de autoria cross-role CR.2)
        $this->fechar($pacientes['2022015'], $draAna, $now->copy()->subDays(60));

        // Lucas Henrique Souza: 1 FECHADO com Dr. Silva (B.3.1 exige que não haja atendimento aberto)
        $this->fechar($pacientes['2024003'], $drSilva, $now->copy()->subDays(90));

        // =====================================================================
        // HISTÓRICO LONGO — bloco B.10.7: mini-card exibe apenas os 5 mais recentes
        // =====================================================================

        // Fernanda Cristina Rocha: 9 atendimentos (8 fechados + 1 aberto)
        // Fechados do mais antigo para o mais recente, depois 1 aberto atual
        $this->fechar($pacientes['2022004'], $drSilva,    $now->copy()->subDays(400));
        $this->fechar($pacientes['2022004'], $draAna,     $now->copy()->subDays(340));
        $this->fechar($pacientes['2022004'], $drMendes,   $now->copy()->subDays(280));
        $this->fechar($pacientes['2022004'], $drPedro,    $now->copy()->subDays(220));
        $this->fechar($pacientes['2022004'], $draCamila,  $now->copy()->subDays(160));
        $this->fechar($pacientes['2022004'], $drSilva,    $now->copy()->subDays(120));
        $this->fechar($pacientes['2022004'], $drBruno,    $now->copy()->subDays(80));
        $this->fechar($pacientes['2022004'], $draAna,     $now->copy()->subDays(40));
        $this->abrir($pacientes['2022004'],  $drSilva); // aberto atual

        // Gabriel Henrique Neves: 7 atendimentos (6 fechados + 1 aberto)
        $this->fechar($pacientes['2022005'], $drMendes,   $now->copy()->subDays(360));
        $this->fechar($pacientes['2022005'], $draPaula,   $now->copy()->subDays(300));
        $this->fechar($pacientes['2022005'], $drPedro,    $now->copy()->subDays(240));
        $this->fechar($pacientes['2022005'], $draJuliana, $now->copy()->subDays(180));
        $this->fechar($pacientes['2022005'], $drSilva,    $now->copy()->subDays(110));
        $this->fechar($pacientes['2022005'], $draCamila,  $now->copy()->subDays(50));
        $this->abrir($pacientes['2022005'],  $drMendes); // aberto atual

        // =====================================================================
        // MIX ABERTO + FECHADO — bloco B.10.4
        // =====================================================================

        // Isabela Martins Costa: 1 aberto + 2 fechados
        $this->fechar($pacientes['2022006'], $draPaula,  $now->copy()->subDays(120));
        $this->fechar($pacientes['2022006'], $drSilva,   $now->copy()->subDays(60));
        $this->abrir($pacientes['2022006'],  $drMendes);

        // Mateus Alves Cunha: 1 aberto + 1 fechado
        $this->fechar($pacientes['2022007'], $drBruno,   $now->copy()->subDays(90));
        $this->abrir($pacientes['2022007'],  $drSilva);

        // Larissa Fernandes Gomes: 1 aberto + 2 fechados
        $this->fechar($pacientes['2022008'], $draCamila, $now->copy()->subDays(150));
        $this->fechar($pacientes['2022008'], $draAna,    $now->copy()->subDays(70));
        $this->abrir($pacientes['2022008'],  $drPedro);

        // =====================================================================
        // SÓ ATENDIMENTOS ABERTOS — 1 atendimento aberto por paciente
        // =====================================================================
        $this->abrir($pacientes['2022009'], $drSilva);   // Rafael Torres Sousa
        $this->abrir($pacientes['2022010'], $draAna);    // Camila Borges Freitas
        $this->abrir($pacientes['2022011'], $drMendes);  // Henrique Melo Cardoso
        $this->abrir($pacientes['2022012'], $draPaula);  // Priscila Castro Moreira
        $this->abrir($pacientes['2022013'], $draCamila); // Daniel Ribeiro Lopes

        // =====================================================================
        // SÓ ATENDIMENTOS FECHADOS — 2-3 fechados por paciente
        // =====================================================================

        // Juliana Carvalho Dias: 3 fechados
        $this->fechar($pacientes['2022014'], $drSilva,   $now->copy()->subDays(200));
        $this->fechar($pacientes['2022014'], $drMendes,  $now->copy()->subDays(100));
        $this->fechar($pacientes['2022014'], $draAna,    $now->copy()->subDays(30));

        // Eduardo Monteiro Pinto: 2 fechados
        $this->fechar($pacientes['2023002'], $drBruno,   $now->copy()->subDays(180));
        $this->fechar($pacientes['2023002'], $drPedro,   $now->copy()->subDays(70));

        // Aline Ferreira Medeiros: 3 fechados
        $this->fechar($pacientes['2023003'], $draCamila, $now->copy()->subDays(250));
        $this->fechar($pacientes['2023003'], $drSilva,   $now->copy()->subDays(130));
        $this->fechar($pacientes['2023003'], $draPaula,  $now->copy()->subDays(45));

        // Bruno Macedo Guimarães: 2 fechados
        $this->fechar($pacientes['2023004'], $draJuliana,$now->copy()->subDays(160));
        $this->fechar($pacientes['2023004'], $drMendes,  $now->copy()->subDays(60));

        // Tatiana Moura Abreu: 2 fechados
        $this->fechar($pacientes['2023005'], $drSilva,   $now->copy()->subDays(140));
        $this->fechar($pacientes['2023005'], $drBruno,   $now->copy()->subDays(55));

        // =====================================================================
        // MULTI-ESPECIALIDADE — paciente atendido por profissionais de áreas distintas
        // Valida a visão completa do histórico quando há mais de uma especialidade
        // =====================================================================

        // Paulo Victor Fonseca: Clínico Geral + Psicologia
        $this->fechar($pacientes['2023006'], $drSilva,   $now->copy()->subDays(100));
        $this->abrir($pacientes['2023006'],  $drPedro);

        // Sabrina Lima Corrêa: Odontologia + Nutrição
        $this->fechar($pacientes['2023007'], $draAna,    $now->copy()->subDays(80));
        $this->abrir($pacientes['2023007'],  $draCamila);

        // =====================================================================
        // GRUPO SEM ATENDIMENTOS — matrículas 2023008..2024002
        // Não criar nenhum atendimento para esses 5 pacientes:
        //   Gustavo Araújo Teixeira  (2023008)
        //   Natália Brito Magalhães  (2023009)
        //   Felipe Rangel Andrade    (2023010)
        //   Adriana Costa Mendes     (2024001)
        //   Rodrigo Barbosa Vieira   (2024002)
        // =====================================================================

        // =====================================================================
        // VARIADOS — pacientes dos grupos de homônimos, sobrenomes iguais,
        // acentuados e gerais: 1-3 atendimentos por paciente para paginação e busca
        // =====================================================================

        // Mapeamento: matrícula → [profissional, status, dias atrás] para cada atendimento
        $atendimentosVariados = [
            // Grupo Homônimos João
            '2021001' => [[$drSilva,   'fechado', 180], [$drMendes,  'aberto', null]],
            '2021002' => [[$draPaula,  'fechado', 140]],
            // Grupo Homônimos Maria
            '2021003' => [[$drPedro,   'fechado', 210], [$draCamila, 'fechado', 90]],
            '2021004' => [[$draAna,    'aberto',  null]],
            // Grupo Homônimos Lucas
            '2021005' => [[$drBruno,   'fechado', 120], [$drSilva,   'aberto', null]],
            '2021006' => [[$draJuliana,'fechado', 200]],
            // Grupo Sobrenome Silva
            '2021007' => [[$drSilva,   'fechado', 160], [$drMendes,  'fechado', 60]],
            '2021008' => [[$draPaula,  'fechado', 95],  [$drBruno,   'aberto', null]],
            // Grupo Sobrenome Santos
            '2021009' => [[$draCamila, 'fechado', 230]],
            '2021010' => [[$drSilva,   'fechado', 170], [$drPedro,   'aberto', null]],
            '2021011' => [[$draAna,    'fechado', 110]],
            // Grupo Sobrenome Oliveira
            '2021012' => [[$drMendes,  'fechado', 260], [$draCamila, 'fechado', 100]],
            '2021013' => [[$drSilva,   'aberto',  null]],
            // Acentuados/compostos
            '2021014' => [[$drBruno,   'fechado', 190]],
            '2021015' => [[$draAna,    'fechado', 135], [$draPaula,  'fechado', 50]],
            '2022001' => [[$drSilva,   'fechado', 220]],
            '2022002' => [[$drPedro,   'fechado', 175], [$draJuliana,'aberto', null]],
            '2022003' => [[$draCamila, 'fechado', 145]],
            // Variados gerais
            '2024004' => [[$drSilva,   'fechado', 310], [$drMendes,  'fechado', 150], [$draAna,   'aberto', null]],
            '2024005' => [[$drBruno,   'fechado', 285]],
            '2024006' => [[$draPaula,  'fechado', 240], [$draCamila, 'aberto', null]],
            '2024007' => [[$drSilva,   'fechado', 195]],
            '2024008' => [[$drPedro,   'fechado', 330], [$draJuliana,'fechado', 165]],
            '2024009' => [[$drMendes,  'aberto',  null]],
            '2024010' => [[$draCamila, 'fechado', 270]],
            '2024011' => [[$drSilva,   'fechado', 215], [$drBruno,   'aberto', null]],
            '2024012' => [[$draAna,    'fechado', 130]],
            '2025001' => [[$drSilva,   'fechado', 355], [$drMendes,  'fechado', 185], [$draPaula,  'fechado', 65]],
            '2025002' => [[$drPedro,   'aberto',  null]],
            '2025003' => [[$draCamila, 'fechado', 300], [$draAna,    'fechado', 75]],
        ];

        foreach ($atendimentosVariados as $matricula => $lista) {
            if (!isset($pacientes[$matricula])) continue;
            $pac = $pacientes[$matricula];

            foreach ($lista as [$prof, $status, $diasAtras]) {
                if ($status === 'aberto') {
                    $this->abrir($pac, $prof);
                } else {
                    $this->fechar($pac, $prof, $now->copy()->subDays($diasAtras));
                }
            }
        }

        $total = Atendimento::count();
        $this->command->info("AtendimentosSeeder: {$total} atendimentos criados.");
    }

    // Cria um atendimento com status 'aberto'
    private function abrir(Paciente $pac, Profissional $prof): Atendimento
    {
        return Atendimento::create([
            'paciente_id'     => $pac->id,
            'profissional_id' => $prof->id,
            'criado_por_id'   => $prof->user_id,
            'status'          => 'aberto',
        ]);
    }

    // Cria um atendimento com status 'fechado' com data de fechamento definida
    private function fechar(Paciente $pac, Profissional $prof, Carbon $fechadoEm): Atendimento
    {
        return Atendimento::create([
            'paciente_id'     => $pac->id,
            'profissional_id' => $prof->id,
            'criado_por_id'   => $prof->user_id,
            'fechado_por_id'  => $prof->user_id,
            'status'          => 'fechado',
            'fechado_em'      => $fechadoEm->toDateTimeString(),
        ]);
    }
}
