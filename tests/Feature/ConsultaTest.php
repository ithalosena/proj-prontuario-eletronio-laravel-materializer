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

    // Formulário de nova consulta retorna 200
    public function test_formulario_de_nova_consulta(): void
    {
        [$user] = $this->criarProfissionalUser();

        $this->actingAs($user)
            ->get('/cadastro-consulta')
            ->assertOk();
    }

    // Criar consulta redireciona para os detalhes da consulta criada
    public function test_criar_consulta(): void
    {
        [$user, $profissional] = $this->criarProfissionalUser();
        [, $paciente]          = $this->criarPacienteUser();

        $response = $this->actingAs($user)->post('/cadastrar-consulta', [
            'profissional_id' => $profissional->id,
            'paciente_id'     => $paciente->id,
            'data_hora'       => now()->format('Y-m-d H:i:s'),
            'tipo'            => 'Clínico Geral',
            'queixa'          => 'Dor de cabeça há 2 dias.',
        ]);

        $consulta = Consulta::first();
        $response->assertRedirect("/consultas/{$consulta->id}");
        $this->assertDatabaseHas('consultas', ['paciente_id' => $paciente->id, 'tipo' => 'Clínico Geral']);
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
            ])
            ->assertRedirect("/atendimentos/{$atendimento->id}");

        $this->assertDatabaseHas('consultas', ['id' => $consulta->id, 'tipo' => 'Retorno']);
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
