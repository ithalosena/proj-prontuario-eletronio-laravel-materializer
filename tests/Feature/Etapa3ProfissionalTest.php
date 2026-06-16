<?php

namespace Tests\Feature;

use App\Models\Atendimento;
use App\Models\Consulta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// v0.10.2 — Etapa 3 (UX coordenado). Testes separados por item: UX-P06+P08, UX-P09.
class Etapa3ProfissionalTest extends TestCase
{
    use RefreshDatabase;

    // ============================================================
    // UX-P06 + UX-P08 — Resumo padronizado da consulta
    // ============================================================

    // O card de consultas do atendimento passa a exibir diagnóstico + badges de exames/prescrições
    public function test_ux_p06_p08_card_consulta_exibe_diagnostico_e_badges(): void
    {
        [$user, $profissional] = $this->criarProfissionalUser();
        [, $paciente]          = $this->criarPacienteUser();

        $atendimento = Atendimento::factory()->create([
            'profissional_id' => $profissional->id,
            'paciente_id'     => $paciente->id,
            'criado_por_id'   => $user->id,
            'status'          => 'aberto',
        ]);
        Consulta::factory()->create([
            'profissional_id' => $profissional->id,
            'paciente_id'     => $paciente->id,
            'atendimento_id'  => $atendimento->id,
            'criado_por_id'   => $user->id,
            'queixa'          => 'Dor de cabeça persistente.',
            'diagnostico'     => 'Cefaleia tensional.',
        ]);

        $this->actingAs($user)
            ->get("/atendimentos/{$atendimento->id}")
            ->assertOk()
            ->assertSee('Cefaleia tensional.')   // diagnóstico agora aparece no card
            ->assertSee('Diagnóstico:')
            ->assertSee('exame(s)')               // badges de contagem
            ->assertSee('prescrição(ões)');
    }

    // ============================================================
    // UX-P09 — Alerta de exame pendente no encerramento
    // ============================================================

    // Com exame sem resultado, o modal de encerramento mostra o alerta de pendência
    public function test_ux_p09_alerta_exame_pendente_aparece(): void
    {
        [$user, $profissional] = $this->criarProfissionalUser();
        [, $paciente]          = $this->criarPacienteUser();

        $atendimento = Atendimento::factory()->create([
            'profissional_id' => $profissional->id,
            'paciente_id'     => $paciente->id,
            'criado_por_id'   => $user->id,
            'status'          => 'aberto',
        ]);
        $consulta = Consulta::factory()->create([
            'profissional_id' => $profissional->id,
            'paciente_id'     => $paciente->id,
            'atendimento_id'  => $atendimento->id,
            'criado_por_id'   => $user->id,
        ]);
        \App\Models\Exame::create([
            'consulta_id'      => $consulta->id,
            'criado_por_id'    => $user->id,
            'tipo'             => 'Hemograma',
            'data_solicitacao' => now(),
            'resultado'        => null, // pendente
        ]);

        $this->actingAs($user)
            ->get("/atendimentos/{$atendimento->id}")
            ->assertOk()
            ->assertSee('exame(s) sem resultado');
    }

    // Sem exames pendentes, o alerta não aparece
    public function test_ux_p09_sem_exame_pendente_nao_mostra_alerta(): void
    {
        [$user, $profissional] = $this->criarProfissionalUser();
        [, $paciente]          = $this->criarPacienteUser();

        $atendimento = Atendimento::factory()->create([
            'profissional_id' => $profissional->id,
            'paciente_id'     => $paciente->id,
            'criado_por_id'   => $user->id,
            'status'          => 'aberto',
        ]);

        $this->actingAs($user)
            ->get("/atendimentos/{$atendimento->id}")
            ->assertOk()
            ->assertDontSee('exame(s) sem resultado');
    }

    // ============================================================
    // UX-P09 — Escopo do select de consultas (exame/prescrição)
    // ============================================================

    // No form de exame, o profissional vê só as próprias consultas (não as de outro profissional)
    public function test_ux_p09_select_exame_escopo_por_profissional(): void
    {
        [$user1, $prof1] = $this->criarProfissionalUser();
        [$user2, $prof2] = $this->criarProfissionalUser();
        [, $pac1]        = $this->criarPacienteUser();
        [, $pac2]        = $this->criarPacienteUser();

        Consulta::factory()->create([
            'profissional_id' => $prof1->id, 'paciente_id' => $pac1->id,
            'criado_por_id' => $user1->id, 'atendimento_id' => null,
        ]);
        Consulta::factory()->create([
            'profissional_id' => $prof2->id, 'paciente_id' => $pac2->id,
            'criado_por_id' => $user2->id, 'atendimento_id' => null,
        ]);

        $this->actingAs($user1)
            ->get('/cadastro-exame')
            ->assertOk()
            ->assertSee($pac1->nome)
            ->assertDontSee($pac2->nome);
    }

    // No form de exame, consultas de atendimento FECHADO não aparecem (princípio read-only)
    public function test_ux_p09_select_exame_exclui_atendimento_fechado(): void
    {
        [$user, $prof] = $this->criarProfissionalUser();
        [, $pac]       = $this->criarPacienteUser();

        $atendFechado = Atendimento::factory()->create([
            'profissional_id' => $prof->id, 'paciente_id' => $pac->id,
            'criado_por_id' => $user->id, 'status' => 'fechado',
        ]);
        Consulta::factory()->create([
            'profissional_id' => $prof->id, 'paciente_id' => $pac->id,
            'criado_por_id' => $user->id, 'atendimento_id' => $atendFechado->id,
        ]);

        $this->actingAs($user)
            ->get('/cadastro-exame')
            ->assertOk()
            ->assertDontSee($pac->nome);
    }
}
