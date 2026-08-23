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
 * DT-MOD-01 → v0.11.1/E3 (modelo CONTAINER · DEC-1) + no-show (DEC-2).
 * Spec: docs_desenvolvimento/testes/fluxo_agendamento_atendimento.md (Parte A + Parte D).
 *
 * Regra-mãe (container): `realizar` ABRE o atendimento (agendado, vazio) e leva à tela do
 * atendimento; as consultas entram inline (sempre com `atendimento_id`). O atendimento pode
 * ficar vazio (estado válido, gerenciado pelo dashboard). Agendado e espontâneo convergem.
 * No-show: estado `nao_compareceu` fecha o agendamento com justificativa, sem atendimento/consulta.
 */
class FluxoAgendamentoTest extends TestCase
{
    use RefreshDatabase;

    private function agendamentoConfirmado(Profissional $prof, Paciente $pac): Agendamento
    {
        return Agendamento::factory()->confirmado()->create([
            'profissional_id' => $prof->id,
            'paciente_id'     => $pac->id,
        ]);
    }

    // Payload de consulta anexada a um atendimento (container: sempre com atendimento_id)
    private function payloadConsulta(Atendimento $at, array $extra = []): array
    {
        return array_merge([
            'atendimento_id'  => $at->id,
            'profissional_id' => $at->profissional_id,
            'paciente_id'     => $at->paciente_id,
            'data_hora'       => now()->format('Y-m-d H:i:s'),
            'tipo'            => 'Clínico Geral',
            'queixa'          => 'Consulta registrada no atendimento.',
        ], $extra);
    }

    // ============================================================
    // D1 — Fluxo Agendado (container)
    // ============================================================

    // D1.1: realizar abre o atendimento (agendado, vazio, aberto) e marca o agendamento realizado
    public function test_realizar_abre_atendimento_agendado_vazio(): void
    {
        [$user, $prof] = $this->criarProfissionalUser();
        [, $pac]       = $this->criarPacienteUser();
        $ag            = $this->agendamentoConfirmado($prof, $pac);

        $resp = $this->actingAs($user)->patch("/agendamentos/{$ag->id}/realizar");

        $this->assertSame(1, Atendimento::count());
        $at = Atendimento::first();
        $this->assertSame('aberto', $at->status);
        $this->assertSame($ag->id, $at->agendamento_id);
        $this->assertSame($pac->id, $at->paciente_id);
        $this->assertSame($prof->id, $at->profissional_id);
        $this->assertTrue($at->isAgendado());
        $this->assertSame(0, Consulta::count());              // vazio: nenhuma consulta ainda
        $this->assertSame('realizado', $ag->fresh()->status);
        $resp->assertRedirect("/atendimentos/{$at->id}");
    }

    // D1.3: realizar e não registrar consulta → atendimento vazio permanece (estado válido)
    public function test_realizar_sem_consulta_deixa_atendimento_vazio(): void
    {
        [$user, $prof] = $this->criarProfissionalUser();
        [, $pac]       = $this->criarPacienteUser();
        $ag            = $this->agendamentoConfirmado($prof, $pac);

        $this->actingAs($user)->patch("/agendamentos/{$ag->id}/realizar");

        $at = Atendimento::first();
        $this->assertSame('aberto', $at->status);
        $this->assertSame(0, $at->consultas()->count());      // vazio é OK no modelo container
    }

    // D1.2: adicionar consulta no atendimento agendado → anexa + liga o agendamento (AgendaLink)
    public function test_adicionar_consulta_no_atendimento_agendado(): void
    {
        [$user, $prof] = $this->criarProfissionalUser();
        [, $pac]       = $this->criarPacienteUser();
        $ag            = $this->agendamentoConfirmado($prof, $pac);

        $this->actingAs($user)->patch("/agendamentos/{$ag->id}/realizar");
        $at = Atendimento::first();

        $resp = $this->actingAs($user)->post('/cadastrar-consulta', $this->payloadConsulta($at));

        $consulta = Consulta::first();
        $this->assertSame($at->id, $consulta->atendimento_id);
        $this->assertSame($consulta->id, $ag->fresh()->consulta_id);   // AgendaLink (1ª consulta)
        $resp->assertRedirect("/atendimentos/{$at->id}");
    }

    // D1.4: realizar idempotente — 2ª vez não cria 2º atendimento, vai para o existente
    public function test_realizar_e_idempotente(): void
    {
        [$user, $prof] = $this->criarProfissionalUser();
        [, $pac]       = $this->criarPacienteUser();
        $ag            = $this->agendamentoConfirmado($prof, $pac);

        $this->actingAs($user)->patch("/agendamentos/{$ag->id}/realizar");
        $at = Atendimento::first();

        $resp = $this->actingAs($user)->patch("/agendamentos/{$ag->id}/realizar");

        $this->assertSame(1, Atendimento::count());
        $resp->assertRedirect("/atendimentos/{$at->id}");
    }

    // Transação: se o exame inline explodir, a consulta é revertida, mas o atendimento
    // (criado no realizar) permanece — não há rollback do atendimento.
    public function test_rollback_da_consulta_nao_afeta_o_atendimento(): void
    {
        [$user, $prof] = $this->criarProfissionalUser();
        [, $pac]       = $this->criarPacienteUser();
        $ag            = $this->agendamentoConfirmado($prof, $pac);
        $this->actingAs($user)->patch("/agendamentos/{$ag->id}/realizar");
        $at = Atendimento::first();

        Exame::creating(function () {
            throw new \RuntimeException('Falha forçada pelo teste (rollback).');
        });

        $this->actingAs($user)->post('/cadastrar-consulta', $this->payloadConsulta($at, [
            'exames' => [['tipo' => 'Hemograma']],
        ]))->assertStatus(500);

        $this->assertSame(0, Consulta::count());
        $this->assertSame(1, Atendimento::count());           // o atendimento continua lá
    }

    // ============================================================
    // Guards de realizar (RBAC + estado)
    // ============================================================

    public function test_nao_realiza_agendamento_pendente(): void
    {
        [$user, $prof] = $this->criarProfissionalUser();
        [, $pac]       = $this->criarPacienteUser();
        $ag = Agendamento::factory()->create([
            'profissional_id' => $prof->id, 'paciente_id' => $pac->id, 'status' => 'pendente',
        ]);

        $this->actingAs($user)->patch("/agendamentos/{$ag->id}/realizar")
            ->assertRedirect()->assertSessionHas('error');
        $this->assertSame(0, Atendimento::count());
    }

    public function test_nao_realiza_agendamento_ja_realizado_sem_atendimento(): void
    {
        [$user, $prof] = $this->criarProfissionalUser();
        [, $pac]       = $this->criarPacienteUser();
        $ag = Agendamento::factory()->create([
            'profissional_id' => $prof->id, 'paciente_id' => $pac->id, 'status' => 'realizado',
        ]);

        $this->actingAs($user)->patch("/agendamentos/{$ag->id}/realizar")
            ->assertRedirect()->assertSessionHas('error');
        $this->assertSame(0, Atendimento::count());
    }

    public function test_profissional_nao_realiza_agendamento_de_outro(): void
    {
        [, $profDono] = $this->criarProfissionalUser();
        [$intruso]    = $this->criarProfissionalUser();
        [, $pac]      = $this->criarPacienteUser();
        $ag = $this->agendamentoConfirmado($profDono, $pac);

        $this->actingAs($intruso)->patch("/agendamentos/{$ag->id}/realizar")->assertForbidden();
        $this->assertSame(0, Atendimento::count());
        $this->assertSame('confirmado', $ag->fresh()->status);
    }

    public function test_paciente_nao_realiza_agendamento(): void
    {
        [, $prof]        = $this->criarProfissionalUser();
        [$userPac, $pac] = $this->criarPacienteUser();
        $ag = $this->agendamentoConfirmado($prof, $pac);

        $this->actingAs($userPac)->patch("/agendamentos/{$ag->id}/realizar")
            ->assertRedirect()->assertSessionHas('error');
        $this->assertSame(0, Atendimento::count());
    }

    // ============================================================
    // D2 — Espontâneo (convergente) + origem
    // ============================================================

    public function test_atendimento_agendado_tem_origem_agendado(): void
    {
        [$user, $prof] = $this->criarProfissionalUser();
        [, $pac]       = $this->criarPacienteUser();
        $ag            = $this->agendamentoConfirmado($prof, $pac);

        $this->actingAs($user)->patch("/agendamentos/{$ag->id}/realizar");

        $at = Atendimento::first();
        $this->assertTrue($at->isAgendado());
        $this->assertSame('agendado', $at->origem);
    }

    public function test_atendimento_espontaneo_tem_origem_espontaneo(): void
    {
        [$user, $prof] = $this->criarProfissionalUser();
        [, $pac]       = $this->criarPacienteUser();

        $this->actingAs($user)->post('/cadastrar-atendimento', [
            'paciente_id'     => $pac->id,
            'profissional_id' => $prof->id,
        ]);

        $at = Atendimento::first();
        $this->assertNull($at->agendamento_id);
        $this->assertFalse($at->isAgendado());
        $this->assertSame('espontaneo', $at->origem);
    }

    public function test_relacao_agendamento_atendimento(): void
    {
        [, $prof] = $this->criarProfissionalUser();
        [, $pac]  = $this->criarPacienteUser();
        $ag       = $this->agendamentoConfirmado($prof, $pac);

        $at = Atendimento::factory()->create([
            'profissional_id' => $prof->id, 'paciente_id' => $pac->id, 'agendamento_id' => $ag->id,
        ]);

        $this->assertTrue($at->agendamento->is($ag));
        $this->assertTrue($ag->fresh()->atendimento->is($at));
    }

    // ============================================================
    // E3b — União: o form de consulta vive DENTRO da tela do atendimento
    // ============================================================

    // Links antigos /cadastro-consulta?atendimento_id=X redirecionam para a tela do atendimento
    public function test_cadastro_consulta_redireciona_para_o_atendimento(): void
    {
        [$user, $prof] = $this->criarProfissionalUser();
        [, $pac]       = $this->criarPacienteUser();
        $at = Atendimento::factory()->create([
            'profissional_id' => $prof->id, 'paciente_id' => $pac->id,
            'criado_por_id'   => $user->id, 'status' => 'aberto',
        ]);

        $this->actingAs($user)
            ->get("/cadastro-consulta?atendimento_id={$at->id}")
            ->assertRedirectContains("/atendimentos/{$at->id}");
    }

    // Atendimento aberto e vazio: form embutido presente e JÁ ABERTO (auto-open);
    // DEC-5: o card "Consultas do atendimento" não aparece enquanto não há consulta
    public function test_atendimento_aberto_vazio_tem_form_embutido_aberto(): void
    {
        [$user, $prof] = $this->criarProfissionalUser();
        [, $pac]       = $this->criarPacienteUser();
        $ag            = $this->agendamentoConfirmado($prof, $pac);
        $this->actingAs($user)->patch("/agendamentos/{$ag->id}/realizar");
        $at = Atendimento::first();

        $this->actingAs($user)->get("/atendimentos/{$at->id}")
            ->assertOk()
            ->assertSee('Registrar Consulta')
            ->assertSee('action="/cadastrar-consulta"', false)
            ->assertSee('collapse show', false)        // sem consulta → form começa aberto
            ->assertSee('id="nova-consulta"', false)
            ->assertDontSee('Consultas do atendimento'); // card da timeline só com consultas
    }

    // E3d: a consulta aceita anotações livres (psicologia e outros perfis) e elas
    // aparecem na linha do tempo do atendimento e nos detalhes da consulta
    public function test_consulta_com_anotacoes_persiste_e_aparece(): void
    {
        [$user, $prof] = $this->criarProfissionalUser();
        [, $pac]       = $this->criarPacienteUser();
        $ag            = $this->agendamentoConfirmado($prof, $pac);
        $this->actingAs($user)->patch("/agendamentos/{$ag->id}/realizar");
        $at = Atendimento::first();

        $this->actingAs($user)->post('/cadastrar-consulta', $this->payloadConsulta($at, [
            'anotacoes' => 'Evolução registrada em campo livre pelo profissional.',
        ]));

        $this->assertDatabaseHas('consultas', [
            'atendimento_id' => $at->id,
            'anotacoes'      => 'Evolução registrada em campo livre pelo profissional.',
        ]);
        $this->actingAs($user)->get("/atendimentos/{$at->id}")
            ->assertOk()
            ->assertSee('Evolução registrada em campo livre pelo profissional.');
    }

    // E3d: os dados de saúde críticos do paciente (ST-15) aparecem na tela do
    // atendimento — alergia em destaque — junto do nome social no formato profissional
    public function test_dados_de_saude_e_nome_social_na_tela_do_atendimento(): void
    {
        [$user, $prof] = $this->criarProfissionalUser();
        [$userPac, $pac] = $this->criarPacienteUser();
        $pac->update([
            'nome_social'               => 'Fê',
            'alergias'                  => 'Penicilina e dipirona',
            'medicamentos_uso_continuo' => 'Losartana 50mg',
            'condicoes_cronicas'        => 'Hipertensão arterial',
        ]);
        $at = Atendimento::factory()->create([
            'profissional_id' => $prof->id, 'paciente_id' => $pac->id,
            'criado_por_id'   => $user->id, 'status' => 'aberto',
        ]);

        $this->actingAs($user)->get("/atendimentos/{$at->id}")
            ->assertOk()
            ->assertSee('Dados de Saúde')
            ->assertSee('Penicilina e dipirona')
            ->assertSee('Losartana 50mg')
            ->assertSee('Hipertensão arterial')
            ->assertSee("Fê ({$pac->nome})");   // regra v0.10.3: "Social (Registro)"
    }

    // Atendimento fechado: sem form embutido (não dá para registrar consulta)
    public function test_atendimento_fechado_nao_tem_form_embutido(): void
    {
        [$user, $prof] = $this->criarProfissionalUser();
        [, $pac]       = $this->criarPacienteUser();
        $at = Atendimento::factory()->create([
            'profissional_id' => $prof->id, 'paciente_id' => $pac->id,
            'criado_por_id'   => $user->id, 'status' => 'fechado',
        ]);

        $this->actingAs($user)->get("/atendimentos/{$at->id}")
            ->assertOk()
            ->assertDontSee('action="/cadastrar-consulta"', false)
            ->assertDontSee('Registrar Consulta');
    }

    // Integridade (container): POST direto não anexa consulta a um atendimento ENCERRADO
    public function test_nao_registra_consulta_em_atendimento_fechado(): void
    {
        [$user, $prof] = $this->criarProfissionalUser();
        [, $pac]       = $this->criarPacienteUser();
        $at = Atendimento::factory()->create([
            'profissional_id' => $prof->id, 'paciente_id' => $pac->id,
            'criado_por_id'   => $user->id, 'status' => 'fechado',
        ]);

        $this->actingAs($user)->post('/cadastrar-consulta', $this->payloadConsulta($at))
            ->assertRedirect("/atendimentos/{$at->id}")
            ->assertSessionHas('error');

        $this->assertSame(0, Consulta::count());   // nada foi criado no atendimento fechado
    }

    // Admin (nivel 1, só leitura) vê a tela do atendimento mas não o form de registrar
    public function test_admin_nao_ve_form_embutido(): void
    {
        $admin    = $this->criarAdmin();
        [, $prof] = $this->criarProfissionalUser();
        [, $pac]  = $this->criarPacienteUser();
        $at = Atendimento::factory()->create([
            'profissional_id' => $prof->id, 'paciente_id' => $pac->id, 'status' => 'aberto',
        ]);

        $this->actingAs($admin)->get("/atendimentos/{$at->id}")
            ->assertOk()
            ->assertDontSee('action="/cadastrar-consulta"', false);
    }

    // ============================================================
    // E1 guard (sobrevive ao container): consulta sempre dentro de atendimento
    // ============================================================

    public function test_consulta_exige_atendimento(): void
    {
        [$user, $prof] = $this->criarProfissionalUser();
        [, $pac]       = $this->criarPacienteUser();

        $this->actingAs($user)->get('/cadastro-consulta')->assertRedirect('/pacientes');

        $this->actingAs($user)->post('/cadastrar-consulta', [
            'profissional_id' => $prof->id, 'paciente_id' => $pac->id,
            'data_hora' => now()->format('Y-m-d H:i:s'), 'tipo' => 'Clínico Geral', 'queixa' => 'x',
        ])->assertRedirect('/pacientes');

        $this->assertSame(0, Consulta::count());
    }

    // Não-regressão: a consulta do fluxo agendado aparece no /meu-prontuario do paciente
    public function test_consulta_agendada_aparece_no_meu_prontuario(): void
    {
        [$user, $prof]   = $this->criarProfissionalUser();
        [$userPac, $pac] = $this->criarPacienteUser();
        $ag = $this->agendamentoConfirmado($prof, $pac);

        $this->actingAs($user)->patch("/agendamentos/{$ag->id}/realizar");
        $at = Atendimento::first();
        $this->actingAs($user)->post('/cadastrar-consulta', $this->payloadConsulta($at, [
            'queixa' => 'Queixa no prontuário do paciente.',
        ]));

        $this->actingAs($userPac)->get('/meu-prontuario')
            ->assertOk()
            ->assertSee('Queixa no prontuário do paciente.');
    }

    // ============================================================
    // D4 — No-show (DEC-2)
    // ============================================================

    // D4.2: marca falta → estado nao_compareceu, com justificativa, sem atendimento/consulta
    public function test_marcar_nao_compareceu(): void
    {
        [$user, $prof] = $this->criarProfissionalUser();
        [, $pac]       = $this->criarPacienteUser();
        $ag            = $this->agendamentoConfirmado($prof, $pac);

        $resp = $this->actingAs($user)->patch("/agendamentos/{$ag->id}/nao-compareceu", [
            'justificativa' => 'Paciente não compareceu e não avisou.',
        ]);

        $ag->refresh();
        $this->assertSame('nao_compareceu', $ag->status);
        $this->assertTrue($ag->isNaoCompareceu());
        $this->assertSame('Paciente não compareceu e não avisou.', $ag->motivo_cancelamento);
        $this->assertNotNull($ag->cancelado_em);
        $this->assertSame(0, Atendimento::count());
        $this->assertSame(0, Consulta::count());
        $resp->assertRedirect('/agendamentos');
    }

    // D4.1: a justificativa é obrigatória
    public function test_no_show_exige_justificativa(): void
    {
        [$user, $prof] = $this->criarProfissionalUser();
        [, $pac]       = $this->criarPacienteUser();
        $ag            = $this->agendamentoConfirmado($prof, $pac);

        $this->actingAs($user)->patch("/agendamentos/{$ag->id}/nao-compareceu", [])
            ->assertSessionHasErrors('justificativa');
        $this->assertSame('confirmado', $ag->fresh()->status);
    }

    // D4.4: só confirmado pode virar falta
    public function test_no_show_so_em_confirmado(): void
    {
        [$user, $prof] = $this->criarProfissionalUser();
        [, $pac]       = $this->criarPacienteUser();
        $ag = Agendamento::factory()->create([
            'profissional_id' => $prof->id, 'paciente_id' => $pac->id, 'status' => 'pendente',
        ]);

        $this->actingAs($user)->patch("/agendamentos/{$ag->id}/nao-compareceu", ['justificativa' => 'x'])
            ->assertRedirect()->assertSessionHas('error');
        $this->assertSame('pendente', $ag->fresh()->status);
    }

    // D4.5: profissional não marca falta de agendamento de outro (403)
    public function test_no_show_de_outro_profissional_bloqueado(): void
    {
        [, $profDono] = $this->criarProfissionalUser();
        [$intruso]    = $this->criarProfissionalUser();
        [, $pac]      = $this->criarPacienteUser();
        $ag = $this->agendamentoConfirmado($profDono, $pac);

        $this->actingAs($intruso)->patch("/agendamentos/{$ag->id}/nao-compareceu", ['justificativa' => 'x'])
            ->assertForbidden();
        $this->assertSame('confirmado', $ag->fresh()->status);
    }

    // D4.5: paciente não acessa a ação (nivel:3)
    public function test_paciente_nao_marca_no_show(): void
    {
        [, $prof]        = $this->criarProfissionalUser();
        [$userPac, $pac] = $this->criarPacienteUser();
        $ag = $this->agendamentoConfirmado($prof, $pac);

        $this->actingAs($userPac)->patch("/agendamentos/{$ag->id}/nao-compareceu", ['justificativa' => 'x'])
            ->assertRedirect()->assertSessionHas('error');
        $this->assertSame('confirmado', $ag->fresh()->status);
    }
}
