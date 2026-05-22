<?php

namespace Tests\Feature\Security;

use App\Models\Agendamento;
use App\Models\Atendimento;
use App\Models\Consulta;
use App\Models\Paciente;
use App\Models\Profissional;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// TC-SEC-01: testa que ConsultaPolicy::view() bloqueia IDOR entre profissionais.
// TC-SEC-02: testa que AgendamentoPolicy::cancelar() bloqueia acesso entre profissionais.
// Adicionados em v0.7.6 (S-01, S-02).
class AcessoConsultaTest extends TestCase
{
    use RefreshDatabase;

    // TC-SEC-01a: profissional A não pode ver consulta do profissional B → 403
    public function test_profissional_nao_pode_ver_consulta_alheia(): void
    {
        [$userA, $profA] = $this->criarProfissionalUser();
        [$userB, $profB] = $this->criarProfissionalUser();
        [, $paciente]    = $this->criarPacienteUser();

        $atendimento = Atendimento::factory()->create([
            'profissional_id' => $profB->id,
            'paciente_id'     => $paciente->id,
        ]);

        $consulta = Consulta::factory()->create([
            'profissional_id' => $profB->id,
            'paciente_id'     => $paciente->id,
            'atendimento_id'  => $atendimento->id,
            'criado_por_id'   => $userB->id,
        ]);

        $this->actingAs($userA)
            ->get("/consultas/{$consulta->id}")
            ->assertForbidden(); // deve retornar 403, não 200
    }

    // TC-SEC-01b: admin pode ver qualquer consulta → 200
    public function test_admin_pode_ver_qualquer_consulta(): void
    {
        $admin = $this->criarAdmin();
        [$userB, $profB] = $this->criarProfissionalUser();
        [, $paciente] = $this->criarPacienteUser();

        $atendimento = Atendimento::factory()->create([
            'profissional_id' => $profB->id,
            'paciente_id'     => $paciente->id,
        ]);

        $consulta = Consulta::factory()->create([
            'profissional_id' => $profB->id,
            'paciente_id'     => $paciente->id,
            'atendimento_id'  => $atendimento->id,
            'criado_por_id'   => $userB->id,
        ]);

        $this->actingAs($admin)
            ->get("/consultas/{$consulta->id}")
            ->assertOk();
    }

    // TC-SEC-01c: profissional A pode ver a própria consulta → 200
    public function test_profissional_pode_ver_propria_consulta(): void
    {
        [$userA, $profA] = $this->criarProfissionalUser();
        [, $paciente] = $this->criarPacienteUser();

        $atendimento = Atendimento::factory()->create([
            'profissional_id' => $profA->id,
            'paciente_id'     => $paciente->id,
        ]);

        $consulta = Consulta::factory()->create([
            'profissional_id' => $profA->id,
            'paciente_id'     => $paciente->id,
            'atendimento_id'  => $atendimento->id,
            'criado_por_id'   => $userA->id,
        ]);

        $this->actingAs($userA)
            ->get("/consultas/{$consulta->id}")
            ->assertOk();
    }

    // TC-SEC-02: profissional A não pode cancelar agendamento do profissional B → 403
    public function test_profissional_nao_cancela_agendamento_alheio(): void
    {
        [$userA]      = $this->criarProfissionalUser();
        [$userB, $profB] = $this->criarProfissionalUser();
        [, $paciente] = $this->criarPacienteUser();

        $agendamento = Agendamento::factory()->create([
            'profissional_id' => $profB->id,
            'paciente_id'     => $paciente->id,
            'criado_por_id'   => $userB->id,
            'status'          => 'pendente',
        ]);

        $this->actingAs($userA)
            ->patch("/agendamentos/{$agendamento->id}/cancelar", [
                'motivo_cancelamento' => 'Tentativa de IDOR',
            ])
            ->assertForbidden();

        // Garantir que o agendamento não foi alterado
        $this->assertDatabaseHas('agendamentos', [
            'id'     => $agendamento->id,
            'status' => 'pendente',
        ]);
    }

    // TC-SEC-02b: profissional A não pode confirmar agendamento do profissional B → 403
    public function test_profissional_nao_confirma_agendamento_alheio(): void
    {
        [$userA]         = $this->criarProfissionalUser();
        [$userB, $profB] = $this->criarProfissionalUser();
        [, $paciente]    = $this->criarPacienteUser();

        $agendamento = Agendamento::factory()->create([
            'profissional_id' => $profB->id,
            'paciente_id'     => $paciente->id,
            'criado_por_id'   => $userB->id,
            'status'          => 'pendente',
        ]);

        $this->actingAs($userA)
            ->patch("/agendamentos/{$agendamento->id}/confirmar")
            ->assertForbidden();
    }

    // TC-SEC-02c: recepcionista pode cancelar qualquer agendamento → não é 403
    public function test_recepcionista_pode_cancelar_qualquer_agendamento(): void
    {
        $recep = $this->criarRecepcionista();
        [, $profB] = $this->criarProfissionalUser();
        [, $paciente] = $this->criarPacienteUser();

        $agendamento = Agendamento::factory()->create([
            'profissional_id' => $profB->id,
            'paciente_id'     => $paciente->id,
            'status'          => 'pendente',
        ]);

        $this->actingAs($recep)
            ->patch("/agendamentos/{$agendamento->id}/cancelar", [
                'motivo_cancelamento' => 'Cancelado pela recepção',
            ])
            ->assertRedirect('/agendamentos');

        $this->assertDatabaseHas('agendamentos', [
            'id'     => $agendamento->id,
            'status' => 'cancelado',
        ]);
    }
}
