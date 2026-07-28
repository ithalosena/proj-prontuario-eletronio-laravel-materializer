<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
 * Sessão autônoma 2026-07-23: verifica que o header padronizado (novo padrão v0.10.x)
 * foi aplicado corretamente em cadastro-paciente, cadastro-profissional e editar_profissional,
 * sem quebrar o fluxo real de submissão dos formulários (regressão).
 */
class PadraoVisualHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_cadastro_paciente_tem_header_novo_sem_btn_default()
    {
        $admin = $this->criarAdmin();

        $response = $this->actingAs($admin)->get('/cadastro-paciente');

        $response->assertOk();
        $response->assertSee('Cadastrar Paciente');
        $response->assertSee('Preencha os dados para criar um novo paciente');
        $response->assertDontSee('btn-default', false);
    }

    public function test_cadastro_profissional_tem_header_novo_sem_btn_default()
    {
        $admin = $this->criarAdmin();

        $response = $this->actingAs($admin)->get('/cadastro-profissional');

        $response->assertOk();
        $response->assertSee('Cadastrar Profissional');
        $response->assertSee('Preencha os dados para criar um novo profissional');
        $response->assertDontSee('btn-default', false);
    }

    public function test_editar_profissional_tem_hero_avatar_sem_btn_default()
    {
        $admin = $this->criarAdmin();
        [, $profissional] = $this->criarProfissionalUser();

        $response = $this->actingAs($admin)->get("/editar-profissional/{$profissional->id}");

        $response->assertOk();
        $response->assertSee('avatar-initial', false);
        $response->assertSee('Editar — ' . $profissional->nome);
        $response->assertDontSee('btn-default', false);
    }

    // Regressão: garante que o formulário AINDA funciona de ponta a ponta após a troca do header
    public function test_cadastro_paciente_ainda_submete_corretamente_apos_mudanca_de_header()
    {
        $admin = $this->criarAdmin();

        $response = $this->actingAs($admin)->post('/cadastrar-paciente', [
            'nome'            => 'Paciente Teste Header',
            'email'           => 'teste.header@example.com',
            'senha'           => 'senha12345',
            'documento'       => '99988877766',
            'data_nascimento' => '2000-01-01',
            'sexo'            => 'F',
            'matricula'       => '2024999',
            'curso'           => 'ADS',
        ]);

        $response->assertRedirect('/pacientes');
        $this->assertDatabaseHas('pacientes', ['matricula' => '2024999']);
    }

    public function test_editar_profissional_ainda_submete_corretamente_apos_mudanca_de_header()
    {
        $admin = $this->criarAdmin();
        [, $profissional] = $this->criarProfissionalUser();

        $response = $this->actingAs($admin)->put("/atualizar-profissional/{$profissional->id}", [
            'nome'                  => 'Nome Atualizado',
            'email'                 => 'atualizado@example.com',
            'contato'               => '(38) 90000-1111',
            'especialidade'         => $profissional->especialidade,
            'registro_profissional' => $profissional->registro_profissional,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('profissionais', ['id' => $profissional->id, 'nome' => 'Nome Atualizado']);
    }
}
