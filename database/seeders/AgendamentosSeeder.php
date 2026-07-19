<?php

namespace Database\Seeders;

use App\Models\Agendamento;
use App\Models\Atendimento;
use App\Models\Consulta;
use App\Models\Paciente;
use App\Models\Profissional;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/*
 * Seed de agendamentos para os próximos ~3 meses (2026-05-21 → 2026-08-21).
 *
 * Design deliberado para testes:
 * - Cada profissional tem padrão de horário distinto (manhã fixa, tarde fixa, misto, etc.)
 * - Dias intencionalmente vazios distribuídos ao longo dos meses (brechas para testar disponibilidade)
 * - Status variados: passado → realizado/cancelado; próximas 2 semanas → confirmado/pendente; futuro → pendente
 * - Slots de 30 min (padrão AgendaConfig), respeitando horário comercial (08:00–17:00)
 * - Sábados com volume menor (08:00–12:00), domingos sem agendamentos
 * - ~120 agendamentos distribuídos entre os 8 profissionais
 */
class AgendamentosSeeder extends Seeder
{
    // Tipos de consulta mapeados por especialidade
    private array $tiposPorEspecialidade = [
        'Clínico Geral'  => ['Clínico Geral', 'Retorno'],
        'Odontologia'    => ['Odontologia', 'Retorno'],
        'Psicologia'     => ['Psicologia', 'Retorno'],
        'Nutrição'       => ['Nutrição', 'Retorno'],
        'Fisioterapia'   => ['Fisioterapia', 'Retorno'],
        'Serviço Social' => ['Serviço Social'],
    ];

    // Motivos de cancelamento realistas
    private array $motivosCancelamento = [
        'Paciente não compareceu.',
        'Remarcado a pedido do paciente.',
        'Profissional indisponível na data.',
        'Conflito de horário com outra consulta.',
        'Paciente cancelou por motivo pessoal.',
        'Remarcado por solicitação da recepção.',
    ];

    // Observações variadas para tornar os dados mais realistas
    private array $observacoes = [
        'Primeira consulta.',
        'Paciente relatou dor crônica — prioridade.',
        'Retorno de consulta anterior.',
        'Encaminhado pelo setor de assistência estudantil.',
        'Agendamento solicitado pelo próprio paciente via sistema.',
        'Paciente com histórico de faltas — confirmar presença.',
        null, null, null, // maioria sem observação
    ];

    public function run(): void
    {
        $profissionais = Profissional::with('user')->get()->keyBy(fn($p) => $p->user->email);
        $pacientes     = Paciente::pluck('id')->toArray();
        $adminUser     = User::where('email', 'admin@prontuif.com')->firstOrFail();
        $recepUser     = User::where('email', 'recepcao@ifnmg.edu.br')->firstOrFail();
        $hoje          = Carbon::today(); // 2026-05-21

        /*
         * Configurações individuais por profissional:
         * slots_dia   → quais horários este profissional atende
         * dias_semana → quais dias da semana tem consulta (1=Seg … 6=Sab)
         * semanas_skip → semanas (0-indexed a partir de hoje) que ficam VAZIAS (brechas de teste)
         * densidade   → proporção de slots preenchidos (1.0 = todos, 0.6 = 60%)
         */
        $configProfissionais = [
            'dr.silva@ifnmg.edu.br' => [
                // Clínico Geral — agenda cheia, horário de manhã fixo
                'slots'        => ['08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30'],
                'dias_semana'  => [1, 2, 3, 4, 5],       // seg a sex
                'semanas_skip' => [4, 9],                 // 2 semanas vazias: ~3ª semana de junho e ~fim de julho
                'densidade'    => 0.85,
                'criado_por'   => $recepUser,
            ],
            'dra.ana@ifnmg.edu.br' => [
                // Odontologia — tarde, seg/qua/sex
                'slots'        => ['13:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30'],
                'dias_semana'  => [1, 3, 5],
                'semanas_skip' => [2, 7, 11],             // 3 semanas vazias distribuídas
                'densidade'    => 0.75,
                'criado_por'   => $recepUser,
            ],
            'dr.pedro@ifnmg.edu.br' => [
                // Psicologia — manhã e tarde, ter/qui
                'slots'        => ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00'],
                'dias_semana'  => [2, 4],
                'semanas_skip' => [3, 8],
                'densidade'    => 0.90,
                'criado_por'   => $adminUser,
            ],
            'dr.mendes@ifnmg.edu.br' => [
                // 2º Clínico Geral — sábados + ter/qui — padrão misto para testes cross-profissional
                'slots'        => ['08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00'],
                'dias_semana'  => [2, 4, 6],              // ter + qui + sáb
                'semanas_skip' => [1, 5, 10],
                'densidade'    => 0.70,
                'criado_por'   => $recepUser,
            ],
            'dra.paula@ifnmg.edu.br' => [
                // 2ª Odontologia — manhã completa seg-sex
                'slots'        => ['08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30'],
                'dias_semana'  => [1, 2, 3, 4, 5],
                'semanas_skip' => [6, 12],
                'densidade'    => 0.65,
                'criado_por'   => $recepUser,
            ],
            'dra.camila@ifnmg.edu.br' => [
                // Nutrição — tarde curta, seg/qua/sex
                'slots'        => ['13:00', '13:30', '14:00', '14:30', '15:00'],
                'dias_semana'  => [1, 3, 5],
                'semanas_skip' => [2, 6, 10],
                'densidade'    => 0.80,
                'criado_por'   => $adminUser,
            ],
            'dr.bruno@ifnmg.edu.br' => [
                // Fisioterapia — tarde, todos os dias úteis
                'slots'        => ['13:00', '13:30', '14:00', '14:30', '15:00', '15:30', '16:00'],
                'dias_semana'  => [1, 2, 3, 4, 5],
                'semanas_skip' => [4, 8, 12],
                'densidade'    => 0.75,
                'criado_por'   => $recepUser,
            ],
            'dra.juliana@ifnmg.edu.br' => [
                // Serviço Social — manhã, apenas seg/qua (agenda mais espaçada)
                'slots'        => ['08:00', '09:00', '10:00', '11:00'],
                'dias_semana'  => [1, 3],
                'semanas_skip' => [1, 3, 7, 11],          // mais brechas — agenda mais restrita
                'densidade'    => 0.85,
                'criado_por'   => $adminUser,
            ],
        ];

        $total = 0;

        foreach ($configProfissionais as $email => $config) {
            $profissional = $profissionais[$email] ?? null;
            if (!$profissional) continue;

            $tipos      = $this->tiposPorEspecialidade[$profissional->especialidade] ?? ['Clínico Geral'];
            $criado_por = $config['criado_por'];

            // Itera semana a semana por 13 semanas (~3 meses)
            for ($semana = 0; $semana < 13; $semana++) {
                // Semanas de brecha — dias completamente vazios (para testar disponibilidade)
                if (in_array($semana, $config['semanas_skip'])) {
                    continue;
                }

                foreach ($config['dias_semana'] as $diaSemana) {
                    // Calcular a data correta para este dia da semana nesta semana
                    $dataBase   = $hoje->copy()->startOfWeek()->addWeeks($semana);
                    $data       = $dataBase->copy()->addDays($diaSemana - 1);

                    // Não criar agendamentos para datas já muito no passado (> 30 dias atrás)
                    if ($data->lt($hoje->copy()->subDays(30))) continue;

                    foreach ($config['slots'] as $slot) {
                        // Aplica densidade — pula slots aleatoriamente para criar brechas de horário
                        if (mt_rand(1, 100) > ($config['densidade'] * 100)) {
                            continue;
                        }

                        $dataHora = Carbon::parse($data->format('Y-m-d') . ' ' . $slot);

                        // Determina status com base na data
                        $status              = 'pendente';
                        $canceladoPorId      = null;
                        $motivoCancelamento  = null;
                        $canceladoEm         = null;

                        if ($dataHora->isPast()) {
                            // Passado: 75% realizado, 25% cancelado
                            $status = mt_rand(1, 4) === 1 ? 'cancelado' : 'realizado';
                        } elseif ($dataHora->lte($hoje->copy()->addDays(14))) {
                            // Próximas 2 semanas: 60% confirmado, 40% pendente
                            $status = mt_rand(1, 5) <= 3 ? 'confirmado' : 'pendente';
                        }
                        // Além de 2 semanas: permanece pendente

                        if ($status === 'cancelado') {
                            $canceladoPorId     = $criado_por->id;
                            $motivoCancelamento = $this->motivosCancelamento[array_rand($this->motivosCancelamento)];
                            $canceladoEm        = $dataHora->copy()->subHours(mt_rand(1, 48));
                        }

                        Agendamento::firstOrCreate(
                            [
                                'profissional_id' => $profissional->id,
                                'data_hora'       => $dataHora,
                            ],
                            [
                                'paciente_id'         => $pacientes[array_rand($pacientes)],
                                'criado_por_id'       => $criado_por->id,
                                'cancelado_por_id'    => $canceladoPorId,
                                'tipo'                => $tipos[array_rand($tipos)],
                                'status'              => $status,
                                'observacao'          => $this->observacoes[array_rand($this->observacoes)],
                                'motivo_cancelamento' => $motivoCancelamento,
                                'cancelado_em'        => $canceladoEm,
                                'consulta_id'         => null,
                            ]
                        );

                        $total++;
                    }
                }
            }
        }

        // =====================================================================
        // DT-MOD-01 (v0.11.0): fecha o ciclo do Modelo A para parte dos
        // agendamentos 'realizado' — cada um ganha o seu atendimento AGENDADO
        // (com agendamento_id) contendo 1 consulta, e recebe consulta_id.
        // Os ~100 atendimentos do AtendimentosSeeder ficam sem agendamento_id
        // (= Espontâneos), então a demo exibe os dois badges (M8.1).
        // =====================================================================
        $realizados = Agendamento::where('status', 'realizado')
            ->whereNull('consulta_id')
            ->orderBy('data_hora')
            ->take(15)
            ->get();

        foreach ($realizados as $i => $ag) {
            $prof = Profissional::find($ag->profissional_id);

            // Os 2 primeiros ficam abertos (badge Agendado + Aberto); o resto fechado
            $fechado = $i >= 2;

            $atendimento = Atendimento::create([
                'paciente_id'     => $ag->paciente_id,
                'profissional_id' => $ag->profissional_id,
                'agendamento_id'  => $ag->id,
                'criado_por_id'   => $prof->user_id,
                'fechado_por_id'  => $fechado ? $prof->user_id : null,
                'status'          => $fechado ? 'fechado' : 'aberto',
                'fechado_em'      => $fechado ? $ag->data_hora->copy()->addHour() : null,
            ]);

            // Alinha a cronologia do atendimento à do agendamento (só estética de demo)
            $atendimento->created_at = $ag->data_hora;
            $atendimento->updated_at = $fechado ? $ag->data_hora->copy()->addHour() : $ag->data_hora;
            $atendimento->save();

            // Modelo A: atendimento nunca fica vazio — nasce com a 1ª consulta
            $consulta = Consulta::create([
                'atendimento_id'  => $atendimento->id,
                'criado_por_id'   => $prof->user_id,
                'profissional_id' => $ag->profissional_id,
                'paciente_id'     => $ag->paciente_id,
                'data_hora'       => $ag->data_hora,
                'tipo'            => $ag->tipo,
                'queixa'          => 'Consulta de demanda agendada (seed DT-MOD-01).',
                'anamnese'        => null,
                'diagnostico'     => null,
                'conduta'         => 'Orientações gerais registradas.',
            ]);

            $ag->update(['consulta_id' => $consulta->id]);
        }

        $this->command->info("✓ DT-MOD-01: {$realizados->count()} agendamentos realizados vinculados a atendimentos Agendados.");

        // Resumo por status para facilitar validação
        $porStatus = Agendamento::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $this->command->info("✓ AgendamentosSeeder: {$total} agendamentos criados/verificados.");
        $this->command->info("  Distribuição: " .
            "pendente={$porStatus['pendente']} | " .
            "confirmado={$porStatus['confirmado']} | " .
            "realizado={$porStatus['realizado']} | " .
            "cancelado={$porStatus['cancelado']}");
        $this->command->info("  Janela: " . now()->subDays(30)->format('d/m/Y') . " → " . now()->addWeeks(13)->format('d/m/Y'));
    }
}
