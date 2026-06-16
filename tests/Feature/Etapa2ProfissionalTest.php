<?php

namespace Tests\Feature;

use App\Models\Atendimento;
use App\Models\Consulta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// v0.10.2 — Etapa 2 (UX rápidos). Testes separados por item: UX-P05, UX-P07, X-01.
// (UX-P02.1 e UX-P03 são mudanças de link/layout; UX-P04 está em AtendimentoTest.)
class Etapa2ProfissionalTest extends TestCase
{
    use RefreshDatabase;

    // ============================================================
    // UX-P05 — Botões da tela de consulta
    // ============================================================

    // O formulário de consulta mostra "Salvar Consulta" e não mostra mais "Agendar para depois"
    public function test_ux_p05_form_consulta_renomeia_botao_e_remove_agendar(): void
    {
        [$user] = $this->criarProfissionalUser();

        $this->actingAs($user)
            ->get('/cadastro-consulta')
            ->assertOk()
            ->assertSee('Salvar Consulta')
            ->assertDontSee('Agendar para depois');
    }

    // Os detalhes da consulta exibem o botão fixo "Ir para Atendimento" quando há atendimento vinculado
    public function test_ux_p05_detalhes_consulta_botao_ir_para_atendimento(): void
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

        $this->actingAs($user)
            ->get("/consultas/{$consulta->id}")
            ->assertOk()
            ->assertSee('Ir para Atendimento');
    }

    // ============================================================
    // UX-P07 — Rótulo "Registrado por" (neutro)
    // ============================================================

    // Os detalhes do atendimento usam "Registrado por" e não mais "Aberto por"
    public function test_ux_p07_detalhes_atendimento_rotulo_registrado_por(): void
    {
        [$user, $profissional] = $this->criarProfissionalUser();
        [, $paciente]          = $this->criarPacienteUser();

        $atendimento = Atendimento::factory()->create([
            'profissional_id' => $profissional->id,
            'paciente_id'     => $paciente->id,
            'criado_por_id'   => $user->id,
        ]);

        $this->actingAs($user)
            ->get("/atendimentos/{$atendimento->id}")
            ->assertOk()
            ->assertSee('Registrado por')
            ->assertDontSee('Aberto por');
    }
}
