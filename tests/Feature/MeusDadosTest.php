<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// C.5 (v0.10.3): tela "Meus Dados" — paciente edita os próprios dados complementares.
class MeusDadosTest extends TestCase
{
    use RefreshDatabase;

    private function payloadPaciente(array $extra = []): array
    {
        return array_merge([
            'cep'        => '39900-000',
            'logradouro' => 'Rua das Flores',
            'numero'     => '100',
            'bairro'     => 'Centro',
            'cidade'     => 'Almenara',
            'uf'         => 'MG',
            'contato'    => '(33) 99999-9999',
            'contato_emergencia_nome'       => 'Maria Responsável',
            'contato_emergencia_telefone'   => '(33) 98888-8888',
            'contato_emergencia_parentesco' => 'mae',
        ], $extra);
    }

    // "Meus Dados" foi unificado em /perfil — GET /meus-dados redireciona
    public function test_meus_dados_redireciona_para_perfil(): void
    {
        [$user] = $this->criarPacienteUser();

        $this->actingAs($user)
            ->get('/meus-dados')
            ->assertRedirect('/perfil');
    }

    // O perfil do paciente exibe a seção de dados complementares (unificado)
    public function test_perfil_do_paciente_mostra_meus_dados(): void
    {
        [$user] = $this->criarPacienteUser();

        $this->actingAs($user)
            ->get('/perfil')
            ->assertOk()
            ->assertViewIs('content.pages.perfil')
            ->assertSee('Meus Dados');
    }

    // Paciente atualiza os próprios dados (inclui clínicos autorrelatados)
    public function test_paciente_atualiza_os_proprios_dados(): void
    {
        [$user, $paciente] = $this->criarPacienteUser();

        // Unificado em /perfil: o salvamento não-AJAX redireciona para /perfil
        $this->actingAs($user)
            ->put('/meus-dados', $this->payloadPaciente(['alergias' => 'Dipirona']))
            ->assertRedirect('/perfil')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('pacientes', [
            'id'       => $paciente->id,
            'cidade'   => 'Almenara',
            'alergias' => 'Dipirona',
        ]);
    }

    // Operador (sem paciente/profissional) é redirecionado ao perfil
    public function test_operador_sem_dados_complementares_redireciona_para_perfil(): void
    {
        $admin = $this->criarAdmin();

        $this->actingAs($admin)
            ->get('/meus-dados')
            ->assertRedirect('/perfil');
    }
}
