<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Testes de CRUD de pacientes (happy-path)
class PacienteTest extends TestCase
{
    use RefreshDatabase;

    // Listagem retorna 200 e exibe o paciente cadastrado
    public function test_listagem_de_pacientes(): void
    {
        $recepcionista = $this->criarRecepcionista();
        [, $paciente]  = $this->criarPacienteUser();

        $this->actingAs($recepcionista)
            ->get('/pacientes')
            ->assertOk()
            ->assertSee($paciente->nome);
    }

    // Perfil do paciente retorna 200 com os dados do paciente
    public function test_exibir_perfil_do_paciente(): void
    {
        $recepcionista = $this->criarRecepcionista();
        [, $paciente]  = $this->criarPacienteUser();

        $this->actingAs($recepcionista)
            ->get("/pacientes/{$paciente->id}")
            ->assertOk()
            ->assertSee($paciente->nome);
    }

    // Formulário de cadastro retorna 200
    public function test_formulario_de_cadastro(): void
    {
        $recepcionista = $this->criarRecepcionista();

        $this->actingAs($recepcionista)
            ->get('/cadastro-paciente')
            ->assertOk();
    }

    // Cadastro cria paciente e usuário, redireciona para /pacientes
    public function test_cadastrar_paciente(): void
    {
        $recepcionista = $this->criarRecepcionista();

        $this->actingAs($recepcionista)
            ->post('/cadastrar-paciente', [
                'nome'            => 'João Teste',
                'email'           => 'joao.teste@aluno.ifnmg.edu.br',
                'senha'           => 'senha123',
                'documento'       => '111.222.333-44',
                'data_nascimento' => '2000-06-15',
                'sexo'            => 'M',
                'matricula'       => 'IF202000001',
                'curso'           => 'ADS',
            ])
            ->assertRedirect('/pacientes');

        $this->assertDatabaseHas('pacientes', ['nome' => 'João Teste', 'matricula' => 'IF202000001']);
    }

    // Atualização salva os novos dados e redireciona
    public function test_atualizar_paciente(): void
    {
        $admin        = $this->criarAdmin();
        [, $paciente] = $this->criarPacienteUser();

        $this->actingAs($admin)
            ->put("/atualizar-paciente/{$paciente->id}", [
                'nome'            => 'Nome Atualizado',
                'documento'       => $paciente->documento,
                'data_nascimento' => $paciente->data_nascimento->format('Y-m-d'),
                'sexo'            => $paciente->sexo,
                'matricula'       => $paciente->matricula,
                'curso'           => $paciente->curso,
            ])
            ->assertRedirect('/pacientes');

        $this->assertDatabaseHas('pacientes', ['id' => $paciente->id, 'nome' => 'Nome Atualizado']);
    }

    // Exclusão aplica soft delete e redireciona para /pacientes
    public function test_excluir_paciente(): void
    {
        $admin        = $this->criarAdmin();
        [, $paciente] = $this->criarPacienteUser();

        $this->actingAs($admin)
            ->delete("/deletar-paciente/{$paciente->id}")
            ->assertRedirect('/pacientes');

        $this->assertSoftDeleted('pacientes', ['id' => $paciente->id]);
    }
}
