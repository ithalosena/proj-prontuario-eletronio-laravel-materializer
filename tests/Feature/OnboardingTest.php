<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================
    // Redirect — paciente sem onboarding vai para /onboarding
    // =========================================================

    public function test_paciente_sem_onboarding_e_redirecionado(): void
    {
        [$user, $paciente] = $this->criarPacienteUser();
        $user->update(['onboarding_completo' => false]);

        $this->actingAs($user)
             ->get('/')
             ->assertRedirect('/onboarding');
    }

    public function test_paciente_com_onboarding_acessa_sistema(): void
    {
        [$user] = $this->criarPacienteUser(); // factory default: onboarding_completo = true

        $this->actingAs($user)
             ->get('/')
             ->assertOk()
             ->assertViewIs('content.pages.dashboard_paciente');
    }

    // =========================================================
    // GET /onboarding — rota isenta do middleware (sem loop)
    // =========================================================

    public function test_rota_onboarding_isenta_do_middleware(): void
    {
        [$user, $paciente] = $this->criarPacienteUser();
        $user->update(['onboarding_completo' => false]);

        $this->actingAs($user)
             ->get('/onboarding')
             ->assertOk(); // não deve redirecionar para /onboarding de novo
    }

    public function test_operador_recebe_wizard_operador(): void
    {
        $admin = $this->criarAdmin();
        $admin->update(['onboarding_completo' => false]);

        $this->actingAs($admin)
             ->get('/onboarding')
             ->assertOk()
             ->assertViewIs('content.pages.onboarding_operador');
    }

    // =========================================================
    // POST /onboarding/salvar/paciente — persiste e conclui
    // =========================================================

    public function test_salvar_onboarding_paciente_marca_completo(): void
    {
        [$user, $paciente] = $this->criarPacienteUser();
        $user->update(['onboarding_completo' => false]);

        $payload = [
            // Passo 0
            'nome'           => 'Maria Teste',
            'documento'      => '111.222.333-44',
            'data_nascimento'=> '2000-05-10',
            'sexo'           => 'F',
            // Passo 1
            'cep'        => '39900-000',
            'logradouro' => 'Rua das Flores',
            'numero'     => '123',
            'bairro'     => 'Centro',
            'cidade'     => 'Almenara',
            'uf'         => 'MG',
            // Passo 2
            'contato'    => '(33) 99999-9999',
            // Passo 4 — emergência primário
            'contato_emergencia_nome'       => 'Ana Souza',
            'contato_emergencia_telefone'   => '(33) 98888-7777',
            'contato_emergencia_parentesco' => 'mae',
        ];

        $this->actingAs($user)
             ->post('/onboarding/salvar/paciente', $payload)
             ->assertRedirect('/');

        $this->assertDatabaseHas('users', [
            'id'                  => $user->id,
            'onboarding_completo' => true,
        ]);
        $this->assertDatabaseHas('pacientes', [
            'id'         => $paciente->id,
            'cidade'     => 'Almenara',
            'logradouro' => 'Rua das Flores',
        ]);
    }

    public function test_menor_de_idade_sem_responsavel_e_rejeitado(): void
    {
        [$user, $paciente] = $this->criarPacienteUser();
        $paciente->update(['data_nascimento' => now()->subYears(15)->format('Y-m-d')]);
        $user->update(['onboarding_completo' => false]);

        $this->actingAs($user)
             ->post('/onboarding/salvar/paciente', [
                 'nome'           => 'João Menor',
                 'documento'      => '999.888.777-66',
                 'data_nascimento'=> now()->subYears(15)->format('Y-m-d'),
                 'sexo'           => 'M',
                 'cep'        => '39900-000',
                 'logradouro' => 'Rua A',
                 'numero'     => '1',
                 'bairro'     => 'Centro',
                 'cidade'     => 'Almenara',
                 'uf'         => 'MG',
                 'contato'    => '(33) 99000-0000',
                 'contato_emergencia_nome'       => 'Pai',
                 'contato_emergencia_telefone'   => '(33) 99000-1111',
                 'contato_emergencia_parentesco' => 'pai',
                 // Sem responsavel_nome — deve falhar
             ])
             ->assertSessionHasErrors('responsavel_nome');
    }

    public function test_emergencia_secundaria_atomica(): void
    {
        [$user, $paciente] = $this->criarPacienteUser();
        $user->update(['onboarding_completo' => false]);

        // Preenche nome do 2º contato mas deixa telefone vazio
        $this->actingAs($user)
             ->post('/onboarding/salvar/paciente', [
                 'nome'           => 'Teste',
                 'documento'      => '111.111.111-11',
                 'data_nascimento'=> '1995-01-01',
                 'sexo'           => 'M',
                 'cep'        => '39900-000',
                 'logradouro' => 'Rua B',
                 'numero'     => '2',
                 'bairro'     => 'Centro',
                 'cidade'     => 'Almenara',
                 'uf'         => 'MG',
                 'contato'    => '(33) 99000-0000',
                 'contato_emergencia_nome'       => 'Contato',
                 'contato_emergencia_telefone'   => '(33) 99000-1111',
                 'contato_emergencia_parentesco' => 'pai',
                 // Bloco B: nome preenchido, telefone vazio
                 'contato_emergencia2_nome'       => 'Segundo Contato',
                 'contato_emergencia2_telefone'   => '',
                 'contato_emergencia2_parentesco' => '',
             ])
             ->assertSessionHasErrors('contato_emergencia2_telefone');
    }

    // =========================================================
    // POST /onboarding/salvar/operador
    // =========================================================

    public function test_salvar_onboarding_operador_marca_completo(): void
    {
        $admin = $this->criarAdmin();
        $admin->update(['onboarding_completo' => false]);

        $this->actingAs($admin)
             ->post('/onboarding/salvar/operador', [
                 'name'  => 'Admin Confirmado',
                 'email' => $admin->email,
             ])
             ->assertRedirect('/');

        $this->assertDatabaseHas('users', [
            'id'                  => $admin->id,
            'onboarding_completo' => true,
        ]);
    }
}
