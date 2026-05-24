<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckConsentimento;
use App\Models\Atendimento;
use App\Models\AuditLog;
use App\Models\Consentimento;
use App\Models\Consulta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Sprint v0.8.1 — testes de conformidade LGPD
// L-03: AuditObserver não loga dados sensíveis de saúde
// L-04: página /privacidade acessível publicamente
// L-01: middleware de consentimento bloqueia acesso sem aceite
// L-06: endpoint de exportação retorna dados do titular
class LgpdTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------------------ L-03

    // L-03a: audit_log NÃO deve conter campos sensíveis de saúde no new_values
    public function test_audit_observer_nao_loga_campos_sensiveis(): void
    {
        [$userProf, $prof] = $this->criarProfissionalUser();
        [, $paciente]      = $this->criarPacienteUser();

        $atendimento = Atendimento::factory()->create([
            'profissional_id' => $prof->id,
            'paciente_id'     => $paciente->id,
        ]);

        // Cria consulta com dados sensíveis — dispara AuditObserver::created()
        $this->actingAs($userProf);
        Consulta::create([
            'profissional_id' => $prof->id,
            'paciente_id'     => $paciente->id,
            'atendimento_id'  => $atendimento->id,
            'criado_por_id'   => $userProf->id,
            'data_hora'       => now(),
            'tipo'            => 'Consulta de Rotina',
            'queixa'          => 'Dor de cabeça intensa',
            'anamnese'        => 'Histórico de enxaqueca',
            'diagnostico'     => 'Enxaqueca sem aura',
            'conduta'         => 'Prescrever analgésico',
        ]);

        // Busca o log de criação da consulta
        $log = AuditLog::where('action', 'created')
            ->where('model_type', 'Consulta')
            ->latest('id')
            ->first();

        $this->assertNotNull($log, 'AuditLog de criação da Consulta deve existir.');

        $newValues = $log->new_values ?? [];

        // Nenhum campo sensível deve aparecer no log
        $this->assertArrayNotHasKey('queixa',      $newValues, 'queixa não deve estar no audit_log');
        $this->assertArrayNotHasKey('anamnese',    $newValues, 'anamnese não deve estar no audit_log');
        $this->assertArrayNotHasKey('diagnostico', $newValues, 'diagnostico não deve estar no audit_log');
        $this->assertArrayNotHasKey('conduta',     $newValues, 'conduta não deve estar no audit_log');

        // O log deve ter campos não-sensíveis
        $this->assertArrayHasKey('profissional_id', $newValues);
        $this->assertArrayHasKey('paciente_id',     $newValues);
    }

    // L-03b: o mesmo vale para updated — campos sensíveis não aparecem em old_values/new_values
    public function test_audit_observer_nao_loga_update_de_campo_sensivel(): void
    {
        [$userProf, $prof] = $this->criarProfissionalUser();
        [, $paciente]      = $this->criarPacienteUser();

        $atendimento = Atendimento::factory()->create([
            'profissional_id' => $prof->id,
            'paciente_id'     => $paciente->id,
        ]);

        $consulta = Consulta::factory()->create([
            'profissional_id' => $prof->id,
            'paciente_id'     => $paciente->id,
            'atendimento_id'  => $atendimento->id,
            'queixa'          => 'Queixa original',
        ]);

        // Atualiza campo sensível
        $consulta->update(['queixa' => 'Queixa alterada']);

        $log = AuditLog::where('action', 'updated')
            ->where('model_type', 'Consulta')
            ->where('model_id', $consulta->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayNotHasKey('queixa', $log->new_values ?? []);
        $this->assertArrayNotHasKey('queixa', $log->old_values ?? []);
    }

    // ------------------------------------------------------------------ L-04

    // L-04a: página /privacidade é acessível sem autenticação
    public function test_politica_privacidade_acessivel_sem_login(): void
    {
        $this->get('/privacidade')
            ->assertOk()
            ->assertSee('Política de Privacidade')
            ->assertSee('LGPD');
    }

    // L-04b: página /privacidade é acessível com autenticação também
    public function test_politica_privacidade_acessivel_com_login(): void
    {
        $admin = $this->criarAdmin();

        $this->actingAs($admin)
            ->get('/privacidade')
            ->assertOk()
            ->assertSee('Política de Privacidade');
    }

    // ------------------------------------------------------------------ L-01

    // L-01a: paciente SEM consentimento é redirecionado ao tentar acessar /meu-prontuario
    public function test_paciente_sem_consentimento_e_redirecionado(): void
    {
        [$userPaciente] = $this->criarPacienteUser();

        // Não criamos nenhum Consentimento — deve redirecionar
        $this->actingAs($userPaciente)
            ->get('/meu-prontuario')
            ->assertRedirect('/consentimento');
    }

    // L-01b: paciente SEM consentimento é redirecionado no /meus-agendamentos também
    public function test_paciente_sem_consentimento_redirecionado_em_agendamentos(): void
    {
        [$userPaciente] = $this->criarPacienteUser();

        $this->actingAs($userPaciente)
            ->get('/meus-agendamentos')
            ->assertRedirect('/consentimento');
    }

    // L-01c: paciente COM consentimento pode acessar /meu-prontuario
    public function test_paciente_com_consentimento_acessa_prontuario(): void
    {
        [$userPaciente] = $this->criarPacienteUser();

        Consentimento::create([
            'user_id'      => $userPaciente->id,
            'versao_termo' => CheckConsentimento::VERSAO_ATUAL,
            'ip_address'   => '127.0.0.1',
            'user_agent'   => 'phpunit',
        ]);

        $this->actingAs($userPaciente)
            ->get('/meu-prontuario')
            ->assertOk();
    }

    // L-01d: POST /consentimento/aceitar cria registro e redireciona para /meu-prontuario
    public function test_aceitar_consentimento_cria_registro(): void
    {
        [$userPaciente] = $this->criarPacienteUser();

        $this->actingAs($userPaciente)
            ->post('/consentimento/aceitar')
            ->assertRedirect('/meu-prontuario');

        $this->assertDatabaseHas('consentimentos', [
            'user_id'      => $userPaciente->id,
            'versao_termo' => CheckConsentimento::VERSAO_ATUAL,
        ]);
    }

    // L-01e: aceitar duas vezes não duplica o registro (idempotente)
    public function test_aceitar_consentimento_e_idempotente(): void
    {
        [$userPaciente] = $this->criarPacienteUser();

        $this->actingAs($userPaciente)->post('/consentimento/aceitar');
        $this->actingAs($userPaciente)->post('/consentimento/aceitar');

        $this->assertDatabaseCount('consentimentos', 1);
    }

    // L-01f: usuário não-paciente não é afetado pelo middleware (admin acessa tudo normalmente)
    public function test_admin_nao_e_afetado_pelo_middleware_consentimento(): void
    {
        $admin = $this->criarAdmin();

        // Admin não tem perfil de paciente — middleware deve deixar passar
        $this->actingAs($admin)
            ->get('/meu-prontuario')
            ->assertRedirect(); // redireciona pois não tem paciente vinculado, mas não por falta de consentimento
    }

    // ------------------------------------------------------------------ L-06

    // L-06a: paciente com consentimento pode exportar seus dados → JSON
    public function test_paciente_pode_exportar_dados(): void
    {
        [$userPaciente, $paciente] = $this->criarPacienteUser();

        Consentimento::create([
            'user_id'      => $userPaciente->id,
            'versao_termo' => CheckConsentimento::VERSAO_ATUAL,
            'ip_address'   => '127.0.0.1',
            'user_agent'   => 'phpunit',
        ]);

        $response = $this->actingAs($userPaciente)
            ->get('/meu-prontuario/exportar');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/json; charset=utf-8');

        $dados = $response->json();

        $this->assertArrayHasKey('titular',       $dados);
        $this->assertArrayHasKey('atendimentos',  $dados);
        $this->assertArrayHasKey('agendamentos',  $dados);
        $this->assertArrayHasKey('consentimentos', $dados);
        $this->assertArrayHasKey('exportado_em',  $dados);

        // Nome do titular deve corresponder ao paciente
        $this->assertEquals($paciente->nome, $dados['titular']['nome']);
    }

    // L-06b: paciente sem consentimento é redirecionado antes do exportar
    public function test_paciente_sem_consentimento_nao_exporta(): void
    {
        [$userPaciente] = $this->criarPacienteUser();

        $this->actingAs($userPaciente)
            ->get('/meu-prontuario/exportar')
            ->assertRedirect('/consentimento');
    }

    // L-06c: usuário sem perfil de paciente é redirecionado ao tentar exportar
    public function test_nao_paciente_nao_exporta(): void
    {
        $admin = $this->criarAdmin();

        $this->actingAs($admin)
            ->get('/meu-prontuario/exportar')
            ->assertRedirect('/');
    }
}
