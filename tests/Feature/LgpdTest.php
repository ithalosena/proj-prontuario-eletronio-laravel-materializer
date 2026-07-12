<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckConsentimento;
use App\Models\Atendimento;
use App\Models\AuditLog;
use App\Models\Consentimento;
use App\Models\Consulta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Sprint v0.8.1 — testes de conformidade LGPD (L-03, L-04, L-01, L-06)
// Sprint v0.8.2 — expansão do middleware para todos os perfis (titular/operador)
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

        $log = AuditLog::where('action', 'created')
            ->where('model_type', 'Consulta')
            ->latest('id')
            ->first();

        $this->assertNotNull($log, 'AuditLog de criação da Consulta deve existir.');

        $newValues = $log->new_values ?? [];

        $this->assertArrayNotHasKey('queixa',      $newValues, 'queixa não deve estar no audit_log');
        $this->assertArrayNotHasKey('anamnese',    $newValues, 'anamnese não deve estar no audit_log');
        $this->assertArrayNotHasKey('diagnostico', $newValues, 'diagnostico não deve estar no audit_log');
        $this->assertArrayNotHasKey('conduta',     $newValues, 'conduta não deve estar no audit_log');

        $this->assertArrayHasKey('profissional_id', $newValues);
        $this->assertArrayHasKey('paciente_id',     $newValues);
    }

    // L-03b: campos sensíveis também não aparecem em old_values/new_values no updated
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

    // L-04a: /privacidade é acessível sem autenticação
    public function test_politica_privacidade_acessivel_sem_login(): void
    {
        $this->get('/privacidade')
            ->assertOk()
            ->assertSee('Política de Privacidade')
            ->assertSee('LGPD');
    }

    // L-04b: /privacidade é acessível com autenticação também
    public function test_politica_privacidade_acessivel_com_login(): void
    {
        $admin = $this->criarAdmin();

        $this->actingAs($admin)
            ->get('/privacidade')
            ->assertOk()
            ->assertSee('Política de Privacidade');
    }

    // ------------------------------------------------------------------ L-01 (titular/paciente)

    // L-01a: paciente SEM consentimento é redirecionado ao tentar acessar /meu-prontuario
    public function test_paciente_sem_consentimento_e_redirecionado(): void
    {
        [$userPaciente] = $this->criarPacienteUser(false);

        $this->actingAs($userPaciente)
            ->get('/meu-prontuario')
            ->assertRedirect('/consentimento');
    }

    // L-01b: paciente SEM consentimento é redirecionado em /meus-agendamentos também
    public function test_paciente_sem_consentimento_redirecionado_em_agendamentos(): void
    {
        [$userPaciente] = $this->criarPacienteUser(false);

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
            'tipo_termo'   => 'titular',
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
            ->post('/consentimento/aceitar', ['tipo_termo' => 'titular'])
            ->assertRedirect('/');

        $this->assertDatabaseHas('consentimentos', [
            'user_id'      => $userPaciente->id,
            'versao_termo' => CheckConsentimento::VERSAO_ATUAL,
            'tipo_termo'   => 'titular',
        ]);
    }

    // L-01e: aceitar duas vezes não duplica o registro (idempotente)
    public function test_aceitar_consentimento_e_idempotente(): void
    {
        [$userPaciente] = $this->criarPacienteUser();

        $this->actingAs($userPaciente)->post('/consentimento/aceitar', ['tipo_termo' => 'titular']);
        $this->actingAs($userPaciente)->post('/consentimento/aceitar', ['tipo_termo' => 'titular']);

        $this->assertDatabaseCount('consentimentos', 1);
    }

    // L-01f: v0.8.2 — admin SEM aceite de operador É interceptado pelo middleware
    // (invertido em v0.8.2: middleware agora cobre todos os perfis 1–5)
    public function test_admin_nao_e_afetado_pelo_middleware_consentimento(): void
    {
        $admin = $this->criarAdmin(false);

        // Admin sem registro de consentimento de operador — middleware deve interceptar
        $this->actingAs($admin)
            ->get('/')
            ->assertRedirect('/consentimento');
    }

    // ------------------------------------------------------------------ L-01 (operador — v0.8.2)

    // v0.8.2: operadores de todos os níveis (1–4) sem aceite são redirecionados
    public function test_operador_sem_aceite_e_redirecionado(): void
    {
        foreach ([1, 2, 3, 4] as $nivel) {
            $user = $this->criarUsuarioComNivel($nivel);

            $this->actingAs($user)
                ->get('/')
                ->assertRedirect('/consentimento');
        }
    }

    // v0.8.2: POST /consentimento/recusar desloga o usuário e registra em audit_log
    public function test_operador_recusa_e_deslogado(): void
    {
        $admin = $this->criarAdmin(false);

        $response = $this->actingAs($admin)
            ->post('/consentimento/recusar');

        $response->assertRedirect('/login');

        // Sessão destruída — usuário não está mais autenticado
        $this->assertGuest();

        // Recusa registrada no audit_log (não na tabela consentimentos)
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action'  => 'consentimento_recusado',
        ]);

        // Tabela consentimentos deve permanecer vazia — recusa não gera registro lá
        $this->assertDatabaseEmpty('consentimentos');
    }

    // v0.8.2: paciente com aceite de titular continua funcionando após expansão do middleware
    public function test_paciente_continua_funcionando_apos_expansao(): void
    {
        [$userPaciente] = $this->criarPacienteUser();

        Consentimento::create([
            'user_id'      => $userPaciente->id,
            'versao_termo' => CheckConsentimento::VERSAO_ATUAL,
            'tipo_termo'   => 'titular',
            'ip_address'   => '127.0.0.1',
            'user_agent'   => 'phpunit',
        ]);

        $this->actingAs($userPaciente)
            ->get('/meu-prontuario')
            ->assertOk();

        $this->actingAs($userPaciente)
            ->get('/meus-agendamentos')
            ->assertOk();
    }

    // ------------------------------------------------------------------ L-06

    // L-06a: paciente com consentimento pode exportar seus dados → JSON
    public function test_paciente_pode_exportar_dados(): void
    {
        [$userPaciente, $paciente] = $this->criarPacienteUser();

        Consentimento::create([
            'user_id'      => $userPaciente->id,
            'versao_termo' => CheckConsentimento::VERSAO_ATUAL,
            'tipo_termo'   => 'titular',
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
        // Portabilidade completa (ST-15): dados de saúde autorrelatados + endereço estruturado
        $this->assertArrayHasKey('dados_de_saude', $dados);
        $this->assertIsArray($dados['titular']['endereco']);

        $this->assertEquals($paciente->nome, $dados['titular']['nome']);
    }

    // L-06b: paciente sem consentimento é redirecionado antes do exportar
    public function test_paciente_sem_consentimento_nao_exporta(): void
    {
        [$userPaciente] = $this->criarPacienteUser(false);

        $this->actingAs($userPaciente)
            ->get('/meu-prontuario/exportar')
            ->assertRedirect('/consentimento');
    }

    // L-06c: usuário sem perfil de paciente não acessa /exportar mesmo com aceite de operador
    public function test_nao_paciente_nao_exporta(): void
    {
        $admin = $this->criarAdmin();

        // Admin precisa ter aceite de operador para passar pelo middleware
        Consentimento::create([
            'user_id'      => $admin->id,
            'versao_termo' => CheckConsentimento::VERSAO_ATUAL,
            'tipo_termo'   => 'operador',
            'ip_address'   => '127.0.0.1',
            'user_agent'   => 'phpunit',
        ]);

        // Controller redireciona para '/' quando usuário não tem perfil de paciente
        $this->actingAs($admin)
            ->get('/meu-prontuario/exportar')
            ->assertRedirect('/');
    }
}
