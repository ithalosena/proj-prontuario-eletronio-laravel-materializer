<?php

namespace Tests\Feature;

use App\Models\Atendimento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Testes do ciclo de vida dos atendimentos (happy-path)
class AtendimentoTest extends TestCase
{
    use RefreshDatabase;

    // Formulário de abertura de atendimento retorna 200
    public function test_formulario_de_abertura_de_atendimento(): void
    {
        [$user] = $this->criarProfissionalUser();

        $this->actingAs($user)
            ->get('/cadastro-atendimento')
            ->assertOk();
    }

    // Criação de atendimento redireciona para a tela de detalhes do atendimento criado
    public function test_criar_atendimento_redireciona_para_detalhes(): void
    {
        [$user, $profissional] = $this->criarProfissionalUser();
        [, $paciente]          = $this->criarPacienteUser();

        $response = $this->actingAs($user)->post('/cadastrar-atendimento', [
            'paciente_id'     => $paciente->id,
            'profissional_id' => $profissional->id,
        ]);

        $atendimento = Atendimento::first();
        $response->assertRedirect("/atendimentos/{$atendimento->id}");
    }

    // Tela de detalhes do atendimento retorna 200 com nome do paciente
    public function test_detalhes_do_atendimento(): void
    {
        [$user, $profissional] = $this->criarProfissionalUser();
        [, $paciente]          = $this->criarPacienteUser();

        $atendimento = Atendimento::factory()->create([
            'paciente_id'     => $paciente->id,
            'profissional_id' => $profissional->id,
            'criado_por_id'   => $user->id,
        ]);

        $this->actingAs($user)
            ->get("/atendimentos/{$atendimento->id}")
            ->assertOk()
            ->assertSee($paciente->nome);
    }

    // Encerrar atendimento muda status para 'fechado' e redireciona
    public function test_encerrar_atendimento(): void
    {
        [$user, $profissional] = $this->criarProfissionalUser();
        [, $paciente]          = $this->criarPacienteUser();

        $atendimento = Atendimento::factory()->create([
            'paciente_id'     => $paciente->id,
            'profissional_id' => $profissional->id,
            'criado_por_id'   => $user->id,
        ]);

        $this->actingAs($user)
            ->patch("/atendimentos/{$atendimento->id}/fechar")
            ->assertRedirect("/atendimentos/{$atendimento->id}");

        $this->assertDatabaseHas('atendimentos', ['id' => $atendimento->id, 'status' => 'fechado']);
    }

    // ANALISE-01 (v0.10.1): Admin é somente leitura — não abre atendimentos (403)
    public function test_admin_nao_abre_atendimento(): void
    {
        $admin = $this->criarAdmin();

        $this->actingAs($admin)->get('/cadastro-atendimento')->assertForbidden();

        [, $paciente]      = $this->criarPacienteUser();
        [, $profissional]  = $this->criarProfissionalUser();
        $this->actingAs($admin)->post('/cadastrar-atendimento', [
            'paciente_id'     => $paciente->id,
            'profissional_id' => $profissional->id,
        ])->assertForbidden();
    }

    // Profissional só enxerga seus próprios atendimentos na listagem
    public function test_profissional_ve_apenas_seus_atendimentos(): void
    {
        [$user1, $profissional1] = $this->criarProfissionalUser();
        [$user2, $profissional2] = $this->criarProfissionalUser();
        [, $paciente]            = $this->criarPacienteUser();

        // Atendimento do profissional 1
        Atendimento::factory()->create([
            'paciente_id'     => $paciente->id,
            'profissional_id' => $profissional1->id,
            'criado_por_id'   => $user1->id,
        ]);

        // Atendimento do profissional 2 — não deve aparecer para profissional 1
        Atendimento::factory()->create([
            'paciente_id'     => $paciente->id,
            'profissional_id' => $profissional2->id,
            'criado_por_id'   => $user2->id,
        ]);

        $response = $this->actingAs($user1)->get('/atendimentos');
        $response->assertOk()->assertSee($profissional1->nome);

        // Nome do profissional 2 não deve aparecer na listagem do profissional 1
        $response->assertDontSee($profissional2->nome);
    }
}
