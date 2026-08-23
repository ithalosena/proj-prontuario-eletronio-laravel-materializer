<?php

namespace Tests\Feature;

use App\Models\Atendimento;
use App\Models\Consulta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Testes de CRUD de consultas (happy-path + regras de autoria)
class ConsultaTest extends TestCase
{
    use RefreshDatabase;

    // E3b (união): a tela standalone foi aposentada — /cadastro-consulta redireciona
    // para a tela do atendimento, onde o formulário agora vive embutido
    public function test_formulario_de_nova_consulta(): void
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
            ->get("/cadastro-consulta?atendimento_id={$atendimento->id}")
            ->assertRedirectContains("/atendimentos/{$atendimento->id}");

        // O form embutido está na tela do atendimento
        $this->actingAs($user)
            ->get("/atendimentos/{$atendimento->id}")
            ->assertOk()
            ->assertSee('Registrar Consulta')
            ->assertSee('action="/cadastrar-consulta"', false);
    }

    // Criar consulta (dentro de um atendimento) redireciona para os detalhes da consulta
    public function test_criar_consulta(): void
    {
        [$user, $profissional] = $this->criarProfissionalUser();
        [, $paciente]          = $this->criarPacienteUser();

        $atendimento = Atendimento::factory()->create([
            'profissional_id' => $profissional->id,
            'paciente_id'     => $paciente->id,
            'criado_por_id'   => $user->id,
            'status'          => 'aberto',
        ]);

        $response = $this->actingAs($user)->post('/cadastrar-consulta', [
            'atendimento_id'  => $atendimento->id,
            'profissional_id' => $profissional->id,
            'paciente_id'     => $paciente->id,
            'data_hora'       => now()->format('Y-m-d H:i:s'),
            'tipo'            => 'Clínico Geral',
            'queixa'          => 'Dor de cabeça há 2 dias.',
        ]);

        $consulta = Consulta::first();
        // E3 (container): a consulta aterrissa de volta na tela do atendimento
        $response->assertRedirect("/atendimentos/{$atendimento->id}");
        $this->assertDatabaseHas('consultas', [
            'paciente_id'    => $paciente->id,
            'tipo'           => 'Clínico Geral',
            'atendimento_id' => $atendimento->id,
        ]);
    }

    // E1 (v0.11.1): consulta sem atendimento nem agendamento é bloqueada (fim da consulta órfã)
    public function test_consulta_sem_atendimento_e_bloqueada(): void
    {
        [$user, $profissional] = $this->criarProfissionalUser();
        [, $paciente]          = $this->criarPacienteUser();

        // GET do formulário sem contexto → redireciona para a listagem de pacientes
        $this->actingAs($user)
            ->get('/cadastro-consulta')
            ->assertRedirect('/pacientes');

        // POST sem contexto → bloqueado, nada é criado
        $this->actingAs($user)->post('/cadastrar-consulta', [
            'profissional_id' => $profissional->id,
            'paciente_id'     => $paciente->id,
            'data_hora'       => now()->format('Y-m-d H:i:s'),
            'tipo'            => 'Clínico Geral',
            'queixa'          => 'Consulta órfã bloqueada.',
        ])->assertRedirect('/pacientes');

        $this->assertSame(0, Consulta::count());
    }

    // ANALISE-01 (v0.10.1): Admin é somente leitura — não registra consultas (403)
    public function test_admin_nao_registra_consulta(): void
    {
        $admin = $this->criarAdmin();

        $this->actingAs($admin)->get('/cadastro-consulta')->assertForbidden();

        [, $profissional] = $this->criarProfissionalUser();
        [, $paciente]     = $this->criarPacienteUser();
        $this->actingAs($admin)->post('/cadastrar-consulta', [
            'profissional_id' => $profissional->id,
            'paciente_id'     => $paciente->id,
            'data_hora'       => now()->format('Y-m-d H:i:s'),
            'tipo'            => 'Clínico Geral',
            'queixa'          => 'Teste admin bloqueado.',
        ])->assertForbidden();
    }

    // Detalhes da consulta retorna 200 com o tipo da consulta
    public function test_detalhes_da_consulta(): void
    {
        [$user, $profissional] = $this->criarProfissionalUser();
        [, $paciente]          = $this->criarPacienteUser();

        $consulta = Consulta::factory()->create([
            'profissional_id' => $profissional->id,
            'paciente_id'     => $paciente->id,
            'criado_por_id'   => $user->id,
        ]);

        $this->actingAs($user)
            ->get("/consultas/{$consulta->id}")
            ->assertOk()
            ->assertSee($consulta->tipo);
    }

    // Autor pode editar consulta em atendimento aberto
    public function test_autor_pode_editar_consulta(): void
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
            ->put("/atualizar-consulta/{$consulta->id}", [
                'data_hora'  => now()->format('Y-m-d H:i:s'),
                'tipo'       => 'Retorno',
                'queixa'     => 'Queixa atualizada.',
                'anotacoes'  => 'Anotação livre editada na consulta.', // E3d
            ])
            ->assertRedirect("/atendimentos/{$atendimento->id}");

        // E3d: o campo de registro livre também é editável (não só no cadastro)
        $this->assertDatabaseHas('consultas', [
            'id'        => $consulta->id,
            'tipo'      => 'Retorno',
            'anotacoes' => 'Anotação livre editada na consulta.',
        ]);
    }

    // Autor pode excluir consulta em atendimento aberto; registro some da listagem
    public function test_autor_pode_excluir_consulta(): void
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
            ->delete("/deletar-consulta/{$consulta->id}")
            ->assertRedirect('/consultas');

        $this->assertSoftDeleted('consultas', ['id' => $consulta->id]);
    }
}
