<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Testes de controle de acesso por nível (RBAC via CheckNivel middleware)
class RbacTest extends TestCase
{
    use RefreshDatabase;

    // Paciente (nivel 5) não consegue acessar /atendimentos (exige nivel <= 3)
    public function test_paciente_nao_acessa_atendimentos(): void
    {
        [$user] = $this->criarPacienteUser();

        $this->actingAs($user)
            ->get('/atendimentos')
            ->assertRedirect();
    }

    // Paciente não consegue acessar /pacientes (exige nivel <= 4)
    public function test_paciente_nao_acessa_listagem_pacientes(): void
    {
        [$user] = $this->criarPacienteUser();

        $this->actingAs($user)
            ->get('/pacientes')
            ->assertRedirect();
    }

    // Recepcionista (nivel 4) não consegue acessar /atendimentos (exige nivel <= 3)
    public function test_recepcionista_nao_acessa_atendimentos(): void
    {
        $user = $this->criarRecepcionista();

        $this->actingAs($user)
            ->get('/atendimentos')
            ->assertRedirect();
    }

    // Recepcionista acessa /pacientes normalmente
    public function test_recepcionista_acessa_listagem_pacientes(): void
    {
        $user = $this->criarRecepcionista();

        $this->actingAs($user)
            ->get('/pacientes')
            ->assertOk();
    }

    // Profissional (nivel 3) acessa /atendimentos normalmente
    public function test_profissional_acessa_atendimentos(): void
    {
        [$user] = $this->criarProfissionalUser();

        $this->actingAs($user)
            ->get('/atendimentos')
            ->assertOk();
    }

    // Profissional não consegue acessar /profissionais (exige nivel <= 1)
    public function test_profissional_nao_acessa_crud_profissionais(): void
    {
        [$user] = $this->criarProfissionalUser();

        $this->actingAs($user)
            ->get('/profissionais')
            ->assertRedirect();
    }

    // Admin (nivel 1) acessa /profissionais normalmente
    public function test_admin_acessa_crud_profissionais(): void
    {
        $user = $this->criarAdmin();

        $this->actingAs($user)
            ->get('/profissionais')
            ->assertOk();
    }
}
