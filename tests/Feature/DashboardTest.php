<?php

namespace Tests\Feature;

use App\Models\Agendamento;
use App\Models\Atendimento;
use App\Models\Consulta;
use App\Models\Role;
use App\Services\RelatorioConformidadeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================
    // Admin (nivel 1)
    // =========================================================

    public function test_admin_acessa_dashboard_admin(): void
    {
        $admin = $this->criarAdmin();

        $this->actingAs($admin)
             ->get('/')
             ->assertOk()
             ->assertViewIs('content.pages.dashboard_admin')
             ->assertViewHas('totalPacientes')
             ->assertViewHas('totalProfissionais')
             ->assertViewHas('atendimentosAbertos')
             ->assertViewHas('consultasNoMes')
             ->assertViewHas('auditLogs')
             ->assertViewHas('conformidade');
    }

    // =========================================================
    // Profissional (nivel 3)
    // =========================================================

    public function test_profissional_acessa_dashboard_profissional(): void
    {
        [$user, $profissional] = $this->criarProfissionalUser();
        // Cria segundo profissional para garantir isolamento
        [$outroUser, $outroProfissional] = $this->criarProfissionalUser();

        $this->actingAs($user)
             ->get('/')
             ->assertOk()
             ->assertViewIs('content.pages.dashboard_profissional')
             ->assertViewHas('profissional', $profissional)
             ->assertViewHas('agendaSemana', function ($agenda) use ($profissional) {
                 // Todos os itens da agenda pertencem ao profissional logado
                 return $agenda->every(
                     fn($a) => $a->profissional_id === $profissional->id
                 );
             })
             ->assertViewHas('ultimasConsultas', function ($consultas) use ($profissional) {
                 return $consultas->every(
                     fn($c) => $c->profissional_id === $profissional->id
                 );
             });
    }

    // =========================================================
    // Paciente (nivel 5)
    // =========================================================

    public function test_paciente_acessa_dashboard_paciente(): void
    {
        [$user, $paciente] = $this->criarPacienteUser();

        $this->actingAs($user)
             ->get('/')
             ->assertOk()
             ->assertViewIs('content.pages.dashboard_paciente')
             ->assertViewHas('paciente', $paciente)
             ->assertViewHas('historicoConsultas')
             ->assertViewHas('prescricoesRecentes');
    }

    // =========================================================
    // Coordenador (nivel 2) — dashboard contextual (v0.9.1b)
    // =========================================================

    public function test_coordenador_acessa_dashboard_coordenador(): void
    {
        $user = $this->criarUsuarioComNivel(2);
        $this->criarConsentimento($user, 'operador');

        $this->actingAs($user)
             ->get('/')
             ->assertOk()
             ->assertViewIs('content.pages.dashboard_coordenador')
             ->assertViewHas('totalProfissionais')
             ->assertViewHas('consultasHoje')
             ->assertViewHas('taxaOcupacao')
             ->assertViewHas('statusCounts')
             ->assertViewHas('top5Profissionais')
             ->assertViewHas('pendentesConfirmacao');
    }

    // =========================================================
    // Recepcionista (nivel 4) — dashboard contextual (v0.9.1b)
    // =========================================================

    public function test_recepcionista_acessa_dashboard_recepcionista(): void
    {
        $recepcionista = $this->criarRecepcionista();

        $this->actingAs($recepcionista)
             ->get('/')
             ->assertOk()
             ->assertViewIs('content.pages.dashboard_recepcionista')
             ->assertViewHas('proximosCheckins')
             ->assertViewHas('agendamentosHoje')
             ->assertViewHas('confirmacoesPendentes')
             ->assertViewHas('chegadasDoDia')
             ->assertViewHas('confirmacoesFazer');
    }

    // =========================================================
    // Widget LGPD — 4 buckets para admin; outros perfis não recebem
    // =========================================================

    public function test_widget_lgpd_retorna_quatro_buckets_para_admin(): void
    {
        $admin = $this->criarAdmin();

        $response = $this->actingAs($admin)->get('/');
        $response->assertOk();

        // A variável $conformidade deve existir e ter os 4 buckets
        $response->assertViewHas('conformidade', function (array $c): bool {
            return array_key_exists('aceitaram', $c)
                && array_key_exists('recusaram', $c)
                && array_key_exists('nunca_acessaram', $c)
                && array_key_exists('versao_antiga', $c)
                && is_int($c['aceitaram'])
                && is_int($c['recusaram'])
                && is_int($c['nunca_acessaram'])
                && is_int($c['versao_antiga']);
        });

        // Paciente NÃO recebe a variável $conformidade em sua view
        [$pacienteUser] = $this->criarPacienteUser();
        $this->actingAs($pacienteUser)
             ->get('/')
             ->assertOk()
             ->assertViewMissing('conformidade');
    }
}
