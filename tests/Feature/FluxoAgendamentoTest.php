<?php

namespace Tests\Feature;

use App\Models\Agendamento;
use App\Models\Atendimento;
use App\Models\Consulta;
use App\Models\Exame;
use App\Models\Paciente;
use App\Models\Profissional;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
 * DT-MOD-01 (Modelo A · v0.11.0) — Fluxo Agendamento → Atendimento → Consulta.
 *
 * Spec: docs_desenvolvimento/testes/fluxo_agendamento_atendimento.md (Parte A).
 * Regra-mãe: o atendimento nasce SÓ quando a 1ª consulta é salva (nunca vazio);
 * origem = presença de agendamento_id (Agendado × Espontâneo); 1 agendamento = 1
 * atendimento; retorno = novo atendimento (ST-Retorno, fora desta sprint).
 */
class FluxoAgendamentoTest extends TestCase
{
    use RefreshDatabase;

    // Agendamento confirmado do profissional/paciente informados
    private function agendamentoConfirmado(Profissional $profissional, Paciente $paciente): Agendamento
    {
        return Agendamento::factory()->confirmado()->create([
            'profissional_id' => $profissional->id,
            'paciente_id'     => $paciente->id,
        ]);
    }

    // Payload mínimo do POST /cadastrar-consulta vindo de um agendamento
    // (espelha os campos hidden que o form contextual envia)
    private function payloadConsulta(Agendamento $agendamento, array $extra = []): array
    {
        return array_merge([
            'agendamento_id'  => $agendamento->id,
            'profissional_id' => $agendamento->profissional_id,
            'paciente_id'     => $agendamento->paciente_id,
            'data_hora'       => now()->format('Y-m-d H:i:s'),
            'tipo'            => 'Clínico Geral',
            'queixa'          => 'Queixa registrada a partir do agendamento.',
        ], $extra);
    }

    // ============================================================
    // A.1 — Criação atômica (o coração do modelo)
    // ============================================================

    // A.1.1: realizar + salvar a consulta cria o atendimento aberto vinculado,
    // com paciente/profissional herdados, e o agendamento vira realizado
    public function test_salvar_consulta_de_agendamento_cria_atendimento_agendado(): void
    {
        [$user, $profissional] = $this->criarProfissionalUser();
        [, $paciente]          = $this->criarPacienteUser();
        $agendamento           = $this->agendamentoConfirmado($profissional, $paciente);

        $response = $this->actingAs($user)
            ->post('/cadastrar-consulta', $this->payloadConsulta($agendamento));

        // Exatamente 1 atendimento, aberto, apontando para o agendamento
        $this->assertSame(1, Atendimento::count());
        $atendimento = Atendimento::first();
        $this->assertSame('aberto', $atendimento->status);
        $this->assertSame($agendamento->id, $atendimento->agendamento_id);
        $this->assertSame($paciente->id, $atendimento->paciente_id);
        $this->assertSame($profissional->id, $atendimento->profissional_id);

        // A consulta nasceu DENTRO do atendimento
        $consulta = Consulta::first();
        $this->assertSame($atendimento->id, $consulta->atendimento_id);

        // O agendamento fechou o ciclo: realizado + consulta vinculada
        $agendamento->refresh();
        $this->assertSame('realizado', $agendamento->status);
        $this->assertSame($consulta->id, $agendamento->consulta_id);

        // Aterrissa nos detalhes do atendimento recém-criado
        $response->assertRedirect("/atendimentos/{$atendimento->id}");
    }

    // A.1.2: realizar mas NÃO salvar (só acessar o form) não cria nada —
    // o agendamento continua confirmado, cobrando o registro
    public function test_realizar_sem_salvar_nao_cria_nada(): void
    {
        [$user, $profissional] = $this->criarProfissionalUser();
        [, $paciente]          = $this->criarPacienteUser();
        $agendamento           = $this->agendamentoConfirmado($profissional, $paciente);

        // O realizar só redireciona para o formulário contextual
        $this->actingAs($user)
            ->patch("/agendamentos/{$agendamento->id}/realizar")
            ->assertRedirect("/cadastro-consulta?agendamento_id={$agendamento->id}");

        // O formulário abre normalmente (paciente travado pelo agendamento)
        $this->actingAs($user)
            ->get("/cadastro-consulta?agendamento_id={$agendamento->id}")
            ->assertOk()
            ->assertSee($paciente->nome);

        // Abandonou: nenhum atendimento, nenhuma consulta, agendamento intacto
        $this->assertSame(0, Atendimento::count());
        $this->assertSame(0, Consulta::count());
        $this->assertSame('confirmado', $agendamento->fresh()->status);
    }

    // A.1.3: o fluxo agendado não gera consulta órfã (atendimento_id nulo)
    public function test_consulta_de_agendamento_nao_fica_orfa(): void
    {
        [$user, $profissional] = $this->criarProfissionalUser();
        [, $paciente]          = $this->criarPacienteUser();

        // Órfã legada pré-existente (decisão §8: não migrar — fica como está)
        Consulta::factory()->create([
            'profissional_id' => $profissional->id,
            'paciente_id'     => $paciente->id,
            'atendimento_id'  => null,
            'criado_por_id'   => $user->id,
        ]);
        $orfasAntes = Consulta::whereNull('atendimento_id')->count();

        $agendamento = $this->agendamentoConfirmado($profissional, $paciente);
        $this->actingAs($user)
            ->post('/cadastrar-consulta', $this->payloadConsulta($agendamento));

        // Nenhuma órfã nova; a consulta do agendamento tem atendimento
        $this->assertSame($orfasAntes, Consulta::whereNull('atendimento_id')->count());
        $this->assertNotNull(Consulta::latest('id')->first()->atendimento_id);
    }

    // A.1.4: transação — se a gravação falhar no meio (exame explode),
    // NADA é persistido: nem atendimento, nem consulta, e a agenda não muda
    public function test_rollback_nao_deixa_atendimento_orfao(): void
    {
        [$user, $profissional] = $this->criarProfissionalUser();
        [, $paciente]          = $this->criarPacienteUser();
        $agendamento           = $this->agendamentoConfirmado($profissional, $paciente);

        // Força uma exceção no meio da transação (depois do atendimento e da consulta)
        Exame::creating(function () {
            throw new \RuntimeException('Falha forçada pelo teste (rollback).');
        });

        $response = $this->actingAs($user)->post(
            '/cadastrar-consulta',
            $this->payloadConsulta($agendamento, [
                'exames' => [['tipo' => 'Hemograma Completo']],
            ])
        );

        $response->assertStatus(500);

        // Rollback total: banco exatamente como antes
        $this->assertSame(0, Atendimento::count());
        $this->assertSame(0, Consulta::count());
        $agendamento->refresh();
        $this->assertSame('confirmado', $agendamento->status);
        $this->assertNull($agendamento->consulta_id);
    }

    // ============================================================
    // A.2 — Origem: Agendado × Espontâneo
    // ============================================================

    // A.2.1: atendimento criado pelo fluxo agendado tem origem 'agendado'
    public function test_atendimento_do_fluxo_agendado_tem_origem_agendado(): void
    {
        [$user, $profissional] = $this->criarProfissionalUser();
        [, $paciente]          = $this->criarPacienteUser();
        $agendamento           = $this->agendamentoConfirmado($profissional, $paciente);

        $this->actingAs($user)
            ->post('/cadastrar-consulta', $this->payloadConsulta($agendamento));

        $atendimento = Atendimento::first();
        $this->assertTrue($atendimento->isAgendado());
        $this->assertSame('agendado', $atendimento->origem);
    }

    // A.2.2: atendimento aberto avulso (/cadastro-atendimento) é espontâneo
    public function test_atendimento_avulso_tem_origem_espontaneo(): void
    {
        [$user, $profissional] = $this->criarProfissionalUser();
        [, $paciente]          = $this->criarPacienteUser();

        $this->actingAs($user)->post('/cadastrar-atendimento', [
            'paciente_id'     => $paciente->id,
            'profissional_id' => $profissional->id,
        ]);

        $atendimento = Atendimento::first();
        $this->assertNull($atendimento->agendamento_id);
        $this->assertFalse($atendimento->isAgendado());
        $this->assertSame('espontaneo', $atendimento->origem);
    }

    // A.2.3: agendamento_id é fillable e a relação agendamento() resolve
    public function test_relacao_agendamento_do_atendimento(): void
    {
        [, $profissional] = $this->criarProfissionalUser();
        [, $paciente]     = $this->criarPacienteUser();
        $agendamento      = $this->agendamentoConfirmado($profissional, $paciente);

        $atendimento = Atendimento::factory()->create([
            'profissional_id' => $profissional->id,
            'paciente_id'     => $paciente->id,
            'agendamento_id'  => $agendamento->id,
        ]);

        $this->assertTrue($atendimento->agendamento->is($agendamento));
        $this->assertTrue($agendamento->fresh()->atendimento->is($atendimento));
    }

    // ============================================================
    // A.3 — 1 agendamento = 1 atendimento / guardas de estado
    // ============================================================

    // A.3.1: agendamento já realizado não pode ser realizado de novo
    public function test_nao_realiza_agendamento_ja_realizado(): void
    {
        [$user, $profissional] = $this->criarProfissionalUser();
        [, $paciente]          = $this->criarPacienteUser();
        $agendamento           = Agendamento::factory()->create([
            'profissional_id' => $profissional->id,
            'paciente_id'     => $paciente->id,
            'status'          => 'realizado',
        ]);

        // Pela rota realizar
        $this->actingAs($user)
            ->patch("/agendamentos/{$agendamento->id}/realizar")
            ->assertRedirect()
            ->assertSessionHas('error');

        // E direto no store (bypass do form): mesmo guard
        $this->actingAs($user)
            ->post('/cadastrar-consulta', $this->payloadConsulta($agendamento))
            ->assertRedirect('/agendamentos')
            ->assertSessionHas('error');

        $this->assertSame(0, Atendimento::count());
        $this->assertSame(0, Consulta::count());
    }

    // A.3.2: agendamento pendente (não confirmado) também é bloqueado
    public function test_nao_realiza_agendamento_pendente(): void
    {
        [$user, $profissional] = $this->criarProfissionalUser();
        [, $paciente]          = $this->criarPacienteUser();
        $agendamento           = Agendamento::factory()->create([
            'profissional_id' => $profissional->id,
            'paciente_id'     => $paciente->id,
            'status'          => 'pendente',
        ]);

        $this->actingAs($user)
            ->patch("/agendamentos/{$agendamento->id}/realizar")
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->actingAs($user)
            ->post('/cadastrar-consulta', $this->payloadConsulta($agendamento))
            ->assertRedirect('/agendamentos')
            ->assertSessionHas('error');

        $this->assertSame(0, Atendimento::count());
        $this->assertSame(0, Consulta::count());
    }

    // A.3.3: um agendamento realizado tem exatamente 1 atendimento — uma
    // segunda tentativa de salvar consulta pelo mesmo agendamento é bloqueada
    public function test_um_agendamento_gera_exatamente_um_atendimento(): void
    {
        [$user, $profissional] = $this->criarProfissionalUser();
        [, $paciente]          = $this->criarPacienteUser();
        $agendamento           = $this->agendamentoConfirmado($profissional, $paciente);

        // 1ª vez: cria
        $this->actingAs($user)
            ->post('/cadastrar-consulta', $this->payloadConsulta($agendamento));

        // 2ª vez: bloqueada (o agendamento já está realizado)
        $this->actingAs($user)
            ->post('/cadastrar-consulta', $this->payloadConsulta($agendamento))
            ->assertRedirect('/agendamentos')
            ->assertSessionHas('error');

        $this->assertSame(1, Atendimento::where('agendamento_id', $agendamento->id)->count());
        $this->assertSame(1, Consulta::count());
    }

    // ============================================================
    // A.4 — Segurança / RBAC (não regressão do S-02)
    // ============================================================

    // A.4.1: profissional não realiza agendamento de OUTRO profissional (403)
    public function test_profissional_nao_realiza_agendamento_de_outro(): void
    {
        [, $profissionalDono] = $this->criarProfissionalUser();
        [$userIntruso]        = $this->criarProfissionalUser();
        [, $paciente]         = $this->criarPacienteUser();
        $agendamento          = $this->agendamentoConfirmado($profissionalDono, $paciente);

        // Rota realizar: policy update do Agendamento (S-02)
        $this->actingAs($userIntruso)
            ->patch("/agendamentos/{$agendamento->id}/realizar")
            ->assertForbidden();

        // Form contextual e store: mesmos guards
        $this->actingAs($userIntruso)
            ->get("/cadastro-consulta?agendamento_id={$agendamento->id}")
            ->assertForbidden();

        $this->actingAs($userIntruso)
            ->post('/cadastrar-consulta', $this->payloadConsulta($agendamento))
            ->assertForbidden();

        $this->assertSame(0, Atendimento::count());
        $this->assertSame(0, Consulta::count());
        $this->assertSame('confirmado', $agendamento->fresh()->status);
    }

    // A.4.2: paciente (nivel 5) não acessa o realizar (rota nivel:3 —
    // CheckNivel devolve redirect com mensagem de erro, não 403)
    public function test_paciente_nao_realiza_agendamento(): void
    {
        [, $profissional]        = $this->criarProfissionalUser();
        [$userPaciente, $paciente] = $this->criarPacienteUser();
        $agendamento             = $this->agendamentoConfirmado($profissional, $paciente);

        $this->actingAs($userPaciente)
            ->patch("/agendamentos/{$agendamento->id}/realizar")
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(0, Atendimento::count());
        $this->assertSame('confirmado', $agendamento->fresh()->status);
    }

    // ============================================================
    // A.5 — Não-regressão do lado do paciente
    // ============================================================

    // A.5.2: a consulta criada pelo fluxo agendado aparece no /meu-prontuario
    // do paciente, dentro do atendimento (hasManyThrough intacto)
    public function test_consulta_agendada_aparece_no_meu_prontuario(): void
    {
        [$user, $profissional]     = $this->criarProfissionalUser();
        [$userPaciente, $paciente] = $this->criarPacienteUser();
        $agendamento               = $this->agendamentoConfirmado($profissional, $paciente);

        $this->actingAs($user)
            ->post('/cadastrar-consulta', $this->payloadConsulta($agendamento));

        $this->actingAs($userPaciente)
            ->get('/meu-prontuario')
            ->assertOk()
            ->assertSee('Queixa registrada a partir do agendamento.');
    }
}
