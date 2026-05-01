<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\Atendimento;
use App\Models\Consulta;
use App\Models\Exame;
use App\Models\Prescricao;

// Cria consultas, exames e prescrições distribuídas pelos atendimentos existentes.
//
// Distribuição de consultas por atendimento:
//   - ~10% dos atendimentos ficam com 0 consultas (CTA "Registrar Primeira" — UX-13)
//   - ~20% com 1 consulta
//   - ~40% com 2-3 consultas
//   - ~30% com 4-5 consultas (histórico longo e variados)
//
// Atendimentos âncora tratados explicitamente para satisfazer pré-requisitos dos roteiros.
// Demais atendimentos recebem consultas via distribuição pelo índice do atendimento.
//
// Variações de exames e prescrições:
//   - Algumas consultas: exame + prescrição
//   - Algumas: só exame
//   - Algumas: só prescrição
//   - Algumas: nem exame nem prescrição (~20%)
class ConsultasSeeder extends Seeder
{
    // Banco de texto SOAP por especialidade (queixa, anamnese, diagnóstico, conduta)
    private array $soap = [
        'Clínico Geral' => [
            ['queixa' => 'Cefaleia persistente há três dias', 'anamnese' => 'Paciente relata dor de cabeça frontal, sem febre. Nega uso de medicamentos. Histórico de estresse acadêmico.', 'diagnostico' => 'Cefaleia tensional', 'conduta' => 'Prescrito analgésico. Orientação para hidratação e redução do estresse. Retorno em 7 dias se persistir.'],
            ['queixa' => 'Dor de garganta e febre baixa', 'anamnese' => 'Paciente com odinofagia há 2 dias e febre de 37,8 °C. Nega alergias conhecidas. Sem tosse produtiva.', 'diagnostico' => 'Faringite viral', 'conduta' => 'Prescrito antitérmico e anti-inflamatório. Orientação para repouso e hidratação abundante.'],
            ['queixa' => 'Dor lombar ao levantar pela manhã', 'anamnese' => 'Paciente queixa-se de dor na região lombar baixa há 5 dias, piora ao sentar por tempo prolongado. Sem irradiação. Nega trauma.', 'diagnostico' => 'Lombalgia aguda de provável causa postural', 'conduta' => 'Orientação postural e alongamento. Prescrito anti-inflamatório por 5 dias. Encaminhamento para fisioterapia se não houver melhora.'],
            ['queixa' => 'Náuseas e diarreia há 24 horas', 'anamnese' => 'Paciente relata episódios de náusea e evacuações líquidas desde ontem. Sem febre. Possível ingestão de alimento suspeito.', 'diagnostico' => 'Gastroenterocolite viral aguda', 'conduta' => 'Hidratação oral supervisionada. Dieta leve. Prescrito antiemético. Retorno imediato se piorar.'],
            ['queixa' => 'Tosse seca e coriza há 4 dias', 'anamnese' => 'Paciente com quadro gripal instalado progressivamente. Temperatura axilar de 37,5 °C. Sem dispneia.', 'diagnostico' => 'Síndrome gripal', 'conduta' => 'Sintomáticos prescritos. Repouso por 2 dias. Monitorar temperatura; retornar se febre >38,5 °C.'],
            ['queixa' => 'Tontura e cansaço frequente', 'anamnese' => 'Paciente relata episódios de tontura ao levantar rapidamente. Sono irregular. Nega palpitações. Dieta pobre em ferro referida.', 'diagnostico' => 'Hipotensão postural — investigar anemia', 'conduta' => 'Solicitado hemograma. Orientação alimentar. Reavaliação após resultado do exame.'],
        ],
        'Odontologia' => [
            ['queixa' => 'Dor no dente molar inferior esquerdo', 'anamnese' => 'Paciente relata dor espontânea e ao mastigar há 3 dias. Última consulta odontológica há 1 ano.', 'diagnostico' => 'Cárie profunda com comprometimento pulpar no dente 36', 'conduta' => 'Iniciado tratamento endodôntico. Prescrito antibiótico e analgésico. Retorno em 7 dias.'],
            ['queixa' => 'Sangramento nas gengivas ao escovar', 'anamnese' => 'Paciente relata sangramento gengival há 2 semanas. Higiene bucal irregular. Acúmulo de tártaro visível.', 'diagnostico' => 'Gengivite associada a biofilme dental', 'conduta' => 'Realizada raspagem supragengival. Orientação intensiva de higiene bucal. Retorno em 30 dias para reavaliação.'],
            ['queixa' => 'Dor na mandíbula ao acordar', 'anamnese' => 'Paciente relata dor e tensão muscular matinal na região das ATMs. Parceiro relata que o paciente range os dentes à noite.', 'diagnostico' => 'Bruxismo do sono com comprometimento muscular', 'conduta' => 'Encaminhado para confecção de placa oclusal. Orientação sobre hábitos parafuncionais. Analgésico para alívio imediato.'],
            ['queixa' => 'Revisão odontológica de rotina', 'anamnese' => 'Paciente sem queixas. Última consulta há 6 meses. Higiene bucal satisfatória.', 'diagnostico' => 'Saúde bucal satisfatória — sem alterações relevantes', 'conduta' => 'Profilaxia realizada. Aplicação de flúor tópico. Retorno em 6 meses.'],
        ],
        'Psicologia' => [
            ['queixa' => 'Ansiedade e dificuldade de concentração nos estudos', 'anamnese' => 'Paciente relata episódios de ansiedade antes de provas e atividades avaliativas. Sono irregular. Sem histórico de tratamento psicológico anterior.', 'diagnostico' => 'Ansiedade situacional relacionada ao desempenho acadêmico', 'conduta' => 'Iniciado acompanhamento psicológico semanal. Técnicas de respiração diafragmática e reestruturação cognitiva introduzidas.'],
            ['queixa' => 'Dificuldade para dormir e irritabilidade', 'anamnese' => 'Paciente relata insônia de manutenção há 3 semanas. Relata conflitos com colegas de quarto. Humor variável.', 'diagnostico' => 'Estresse agudo com impacto no sono', 'conduta' => 'Orientação de higiene do sono. Psicoeducação sobre estresse. Retorno em 15 dias para avaliação da evolução.'],
            ['queixa' => 'Sentimento de tristeza e desmotivação', 'anamnese' => 'Paciente relata perda de interesse por atividades que antes apreciava, há 4 semanas. Sem ideação suicida. Episódio coincidente com início do semestre letivo.', 'diagnostico' => 'Humor depressivo situacional — monitorar evolução', 'conduta' => 'Sessão de escuta ativa e validação emocional. Planejamento de atividades prazerosas. Reavaliação em 2 semanas.'],
            ['queixa' => 'Dificuldade de se adaptar à vida no campus', 'anamnese' => 'Paciente é calouro e relata saudade de casa e dificuldade de criar vínculos. Nega sofrimento intenso, mas relata sensação de não pertencimento.', 'diagnostico' => 'Processo adaptativo — contexto de primeiro afastamento do ambiente familiar', 'conduta' => 'Sessão de orientação e acolhimento. Indicados grupos de apoio do campus. Retorno mensal para acompanhamento.'],
        ],
        'Nutrição' => [
            ['queixa' => 'Ganho de peso nos últimos 6 meses', 'anamnese' => 'Paciente relata aumento de 8 kg sem mudança intencional de hábitos. Refere dieta irregular, com muita comida rápida e skippar refeições.', 'diagnostico' => 'Sobrepeso (IMC 27,3) — hábitos alimentares inadequados', 'conduta' => 'Elaborado plano alimentar individualizado. Orientação sobre fracionamento das refeições. Retorno em 30 dias com registro alimentar de 7 dias.'],
            ['queixa' => 'Cansaço frequente e queda de cabelo', 'anamnese' => 'Paciente relata fadiga constante e aumento de queda de cabelo. Dieta com baixo consumo de proteínas e ferro. Exames laboratoriais sugeridos.', 'diagnostico' => 'Déficit nutricional — suspeita de anemia ferropriva', 'conduta' => 'Solicitados exames laboratoriais (hemograma, ferritina, TSH). Orientação alimentar para alimentos ricos em ferro. Retorno após resultado.'],
            ['queixa' => 'Consulta preventiva de nutrição', 'anamnese' => 'Paciente saudável buscando orientação alimentar. Sem queixas específicas. Interesse em alimentação saudável para manutenção de peso.', 'diagnostico' => 'Estado nutricional adequado — IMC 22,1', 'conduta' => 'Orientação sobre alimentação equilibrada e variedade nutricional. Dicas para refeições práticas no contexto universitário. Retorno semestral.'],
        ],
        'Fisioterapia' => [
            ['queixa' => 'Dor no joelho após treino de futebol', 'anamnese' => 'Paciente relata dor anterior no joelho direito após atividade física intensa. Crepitação presente ao subir escadas. Sem edema significativo.', 'diagnostico' => 'Tendinite patelar — sobrecarga do aparelho extensor', 'conduta' => 'Iniciado protocolo de fisioterapia com foco em fortalecimento do quadríceps. Crioterapia pós-sessão. Orientação sobre retorno gradual ao esporte.'],
            ['queixa' => 'Dor nas costas que irradia para a perna', 'anamnese' => 'Paciente com lombalgia crônica há 6 meses, piora recente com irradiação para membro inferior direito. Sem déficit motor.', 'diagnostico' => 'Lombalgia com irradiação ciático-femoral — suspeita de protrusão discal', 'conduta' => 'Terapia manual e exercícios de estabilização lombopélvica. Solicitada ressonância magnética para diagnóstico preciso.'],
            ['queixa' => 'Torção do tornozelo esquerdo há 5 dias', 'anamnese' => 'Paciente sofreu entorse por inversão do tornozelo durante atividade esportiva. Edema residual e dificuldade para caminhar.', 'diagnostico' => 'Entorse de tornozelo — grau II (lesão parcial ligamentar)', 'conduta' => 'Técnicas de redução do edema. Mobilização precoce supervisionada. Bandagem funcional aplicada. Alta prevista após 8 sessões.'],
        ],
        'Serviço Social' => [
            ['queixa' => 'Dificuldades financeiras para manutenção no campus', 'anamnese' => 'Aluno em situação de vulnerabilidade socioeconômica. Família de baixa renda. Sem auxílio estudantil vigente. Em risco de abandono do curso.', 'diagnostico' => 'Vulnerabilidade socioeconômica identificada — elegível a programas de assistência', 'conduta' => 'Orientação sobre programas de assistência estudantil (auxílio moradia, alimentação, transporte). Encaminhamento ao setor de assistência estudantil para análise socioeconômica.'],
            ['queixa' => 'Conflitos familiares impactando desempenho acadêmico', 'anamnese' => 'Aluno relata conflitos frequentes com familiares relacionados à escolha do curso e distância de casa. Comprometimento emocional visível. Parcial no semestre atual.', 'diagnostico' => 'Conflito familiar com impacto psicossocial no processo de aprendizagem', 'conduta' => 'Escuta qualificada. Encaminhamento para acompanhamento psicológico. Articulação com coordenação pedagógica para apoio acadêmico.'],
            ['queixa' => 'Dificuldades de convivência no alojamento', 'anamnese' => 'Aluno relata desentendimentos recorrentes com colegas de alojamento. Sensação de exclusão e isolamento. Deseja auxílio para mediação de conflitos.', 'diagnostico' => 'Dificuldade de convivência coletiva — necessita de mediação e orientação', 'conduta' => 'Mediação de conflitos agendada. Orientação sobre regras de convivência. Articulação com equipe de apoio psicossocial do campus.'],
        ],
    ];

    // Banco de exames por especialidade
    private array $tiposExame = [
        'Clínico Geral' => ['Hemograma Completo', 'Raio-X de Tórax', 'Glicemia de Jejum', 'Urina Tipo I', 'ECG'],
        'Odontologia'   => ['Radiografia Panorâmica', 'Radiografia Periapical', 'Bite-wing Digital'],
        'Psicologia'    => ['Avaliação Neuropsicológica'],
        'Nutrição'      => ['Hemograma', 'Ferritina Sérica', 'TSH', 'Lipidograma'],
        'Fisioterapia'  => ['Raio-X', 'Ressonância Magnética', 'Ultrassonografia Musculoesquelética'],
        'Serviço Social'=> [],
    ];

    // Banco de prescrições por especialidade
    private array $prescricoes = [
        'Clínico Geral' => [
            ['med' => 'Paracetamol 750mg',   'dos' => '1 comprimido', 'freq' => 'De 8 em 8 horas',  'dur' => '5 dias',  'obs' => 'Tomar preferencialmente após refeições.'],
            ['med' => 'Ibuprofeno 400mg',    'dos' => '1 comprimido', 'freq' => 'De 12 em 12 horas','dur' => '3 dias',  'obs' => 'Tomar com estômago cheio. Suspender se houver desconforto gástrico.'],
            ['med' => 'Dipirona 500mg',      'dos' => '1 comprimido', 'freq' => 'De 6 em 6 horas',  'dur' => '3 dias',  'obs' => 'Apenas se febre acima de 38 °C.'],
            ['med' => 'Amoxicilina 500mg',   'dos' => '1 cápsula',    'freq' => 'De 8 em 8 horas',  'dur' => '7 dias',  'obs' => 'Completar o ciclo mesmo com melhora dos sintomas.'],
            ['med' => 'Omeprazol 20mg',      'dos' => '1 cápsula',    'freq' => '1 vez ao dia',     'dur' => '14 dias', 'obs' => 'Tomar em jejum, 30 minutos antes do café da manhã.'],
            ['med' => 'Loratadina 10mg',     'dos' => '1 comprimido', 'freq' => '1 vez ao dia',     'dur' => '10 dias', 'obs' => null],
        ],
        'Odontologia' => [
            ['med' => 'Amoxicilina 500mg',   'dos' => '1 cápsula',    'freq' => 'De 8 em 8 horas',  'dur' => '7 dias',  'obs' => 'Uso profilático pós-procedimento odontológico.'],
            ['med' => 'Ibuprofeno 600mg',    'dos' => '1 comprimido', 'freq' => 'De 8 em 8 horas',  'dur' => '3 dias',  'obs' => 'Tomar com estômago cheio para analgesia pós-procedimento.'],
            ['med' => 'Diclofenaco 50mg',    'dos' => '1 comprimido', 'freq' => 'De 12 em 12 horas','dur' => '5 dias',  'obs' => 'Evitar em pacientes com gastrite.'],
        ],
        'Nutrição' => [
            ['med' => 'Sulfato Ferroso 40mg','dos' => '1 comprimido', 'freq' => '1 vez ao dia',     'dur' => '60 dias', 'obs' => 'Tomar em jejum ou com suco de laranja para melhor absorção.'],
            ['med' => 'Vitamina D 1000UI',   'dos' => '1 cápsula',    'freq' => '1 vez ao dia',     'dur' => '30 dias', 'obs' => 'Tomar com refeição rica em gordura.'],
        ],
        'Psicologia'    => [],
        'Fisioterapia'  => [],
        'Serviço Social'=> [],
    ];

    public function run(): void
    {
        $now = Carbon::now();

        // Carrega todos os atendimentos com profissional e paciente carregados
        $atendimentos = Atendimento::with(['profissional', 'paciente'])->get();

        // Mapeia atendimentos âncora para tratamento explícito
        $ancMaria = $atendimentos->where('paciente.matricula', '2023001')->where('status', 'aberto')->first();
        $ancJoao  = $atendimentos->where('paciente.matricula', '2022015')->where('status', 'fechado')->first();
        $ancLucas = $atendimentos->where('paciente.matricula', '2024003')->where('status', 'fechado')->first();

        // =====================================================================
        // ÂNCORAS — consultas específicas para satisfazer pré-requisitos dos roteiros
        // =====================================================================

        // Maria + Dr. Silva (aberto): 1 consulta para o atendimento já ter conteúdo
        // mas o botão CTA "Registrar Primeira Consulta" NÃO deve aparecer (B.4.6)
        if ($ancMaria) {
            $c = $this->criarConsulta($ancMaria, $now->copy()->subDays(2)->setTime(9, 30), 'Clínico Geral', 0);
            $this->criarExame($c, 'Clínico Geral', 0, $now->copy()->subDays(2));
            $this->criarPrescricao($c, 'Clínico Geral', 0);
        }

        // João + Dra. Ana (fechado): 1 consulta odontológica completa
        if ($ancJoao) {
            $c = $this->criarConsulta($ancJoao, $now->copy()->subDays(60)->setTime(14, 30), 'Odontologia', 0);
            $this->criarExame($c, 'Odontologia', 0, $now->copy()->subDays(60));
            $this->criarPrescricao($c, 'Odontologia', 0);
        }

        // Lucas + Dr. Silva (fechado): 1 consulta clínico geral + exame + prescrição
        if ($ancLucas) {
            $c = $this->criarConsulta($ancLucas, $now->copy()->subDays(90)->setTime(8, 30), 'Clínico Geral', 3);
            $this->criarExame($c, 'Clínico Geral', 2, $now->copy()->subDays(90));
            $this->criarPrescricao($c, 'Clínico Geral', 1);
        }

        // =====================================================================
        // DEMAIS ATENDIMENTOS — distribuição baseada no ID do atendimento
        // Atendimentos âncora já tratados acima são pulados
        // =====================================================================

        $idAncMaria = $ancMaria?->id;
        $idAncJoao  = $ancJoao?->id;
        $idAncLucas = $ancLucas?->id;

        foreach ($atendimentos as $at) {
            // Pula os âncoras já tratados
            if (in_array($at->id, [$idAncMaria, $idAncJoao, $idAncLucas])) continue;

            $esp      = $at->profissional->especialidade ?? 'Clínico Geral';
            $base     = $at->created_at ?? $now->copy()->subDays(30);
            $modIdx   = $at->id % 10;

            // Distribuição de quantidade de consultas por faixa do módulo
            // IDs terminando em 0: 0 consultas (CTA "Registrar Primeira Consulta")
            // IDs terminando em 1: 1 consulta
            // IDs terminando em 2-4: 2 consultas
            // IDs terminando em 5-7: 3 consultas
            // IDs terminando em 8-9: 4 consultas
            $qtd = match(true) {
                $modIdx === 0             => 0,
                $modIdx === 1             => 1,
                $modIdx >= 2 && $modIdx <= 4 => 2,
                $modIdx >= 5 && $modIdx <= 7 => 3,
                default                   => 4,
            };

            for ($j = 0; $j < $qtd; $j++) {
                $dataHora = Carbon::parse($base)->subDays(($qtd - 1 - $j) * 20)->setTime(8 + ($j % 4) * 2, 0);
                $soapIdx  = ($at->id + $j) % count($this->soap[$esp] ?? $this->soap['Clínico Geral']);
                $consulta = $this->criarConsulta($at, $dataHora, $esp, $soapIdx);

                // Distribuição: exame, prescrição, ambos ou nenhum — baseado no módulo
                $varIdx = ($at->id + $j) % 5;

                if ($varIdx === 0) {
                    // Nenhum (consulta simples — ~20%)
                    continue;
                }
                if ($varIdx === 1 || $varIdx === 4) {
                    // Só exame (~40%)
                    $this->criarExame($consulta, $esp, ($at->id + $j) % count($this->tiposExame[$esp] ?: ['Genérico']), Carbon::parse($dataHora));
                }
                if ($varIdx === 2 || $varIdx === 4) {
                    // Prescrição (ou ambos — ~40%)
                    $prescList = $this->prescricoes[$esp] ?? [];
                    if (!empty($prescList)) {
                        $this->criarPrescricao($consulta, $esp, ($at->id + $j) % count($prescList));
                    }
                }
                if ($varIdx === 3) {
                    // Exame com resultado preenchido (~20%)
                    $exame = $this->criarExame($consulta, $esp, 0, Carbon::parse($dataHora));
                    $exame->update([
                        'data_resultado' => Carbon::parse($dataHora)->addDays(3)->toDateString(),
                        'resultado'      => 'Resultado dentro dos parâmetros normais para a faixa etária do paciente.',
                    ]);
                }
            }
        }

        $totalConsultas   = Consulta::count();
        $totalExames      = Exame::count();
        $totalPrescricoes = Prescricao::count();

        $this->command->info("ConsultasSeeder: {$totalConsultas} consultas, {$totalExames} exames, {$totalPrescricoes} prescrições criados.");
    }

    // Cria uma consulta vinculada ao atendimento com o SOAP correto para a especialidade
    private function criarConsulta(Atendimento $at, Carbon $dataHora, string $esp, int $soapIdx): Consulta
    {
        $banco = $this->soap[$esp] ?? $this->soap['Clínico Geral'];
        $s     = $banco[$soapIdx % count($banco)];

        return Consulta::create([
            'atendimento_id'  => $at->id,
            'profissional_id' => $at->profissional_id,
            'paciente_id'     => $at->paciente_id,
            'criado_por_id'   => $at->profissional->user_id,
            'data_hora'       => $dataHora->toDateTimeString(),
            'tipo'            => $esp,
            'queixa'          => $s['queixa'],
            'anamnese'        => $s['anamnese'],
            'diagnostico'     => $s['diagnostico'],
            'conduta'         => $s['conduta'],
        ]);
    }

    // Cria um exame vinculado à consulta; resultado em branco por padrão (status pendente)
    private function criarExame(Consulta $consulta, string $esp, int $tipoIdx, Carbon $data): Exame
    {
        $tipos = $this->tiposExame[$esp] ?? [];
        $tipo  = !empty($tipos) ? $tipos[$tipoIdx % count($tipos)] : 'Exame Geral';

        return Exame::create([
            'consulta_id'     => $consulta->id,
            'criado_por_id'   => $consulta->criado_por_id,
            'tipo'            => $tipo,
            'observacao'      => 'Solicitado para complementar avaliação clínica.',
            'data_solicitacao'=> $data->toDateString(),
        ]);
    }

    // Cria uma prescrição vinculada à consulta
    private function criarPrescricao(Consulta $consulta, string $esp, int $prescIdx): void
    {
        $lista = $this->prescricoes[$esp] ?? [];
        if (empty($lista)) return;

        $p = $lista[$prescIdx % count($lista)];

        Prescricao::create([
            'consulta_id'     => $consulta->id,
            'criado_por_id'   => $consulta->criado_por_id,
            'nome_medicamento'=> $p['med'],
            'dosagem'         => $p['dos'],
            'frequencia'      => $p['freq'],
            'duracao'         => $p['dur'],
            'observacao'      => $p['obs'],
        ]);
    }
}
