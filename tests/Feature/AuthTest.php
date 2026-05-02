<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Testes do fluxo de autenticação: login, logout e proteção de rotas
class AuthTest extends TestCase
{
    use RefreshDatabase;

    // Usuário não autenticado é redirecionado para /login ao acessar rota protegida
    public function test_usuario_nao_autenticado_e_redirecionado(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    // Login com credenciais válidas redireciona para o dashboard
    public function test_login_com_credenciais_validas(): void
    {
        User::factory()->create([
            'email'    => 'teste@ifnmg.edu.br',
            'password' => 'senha123',
        ]);

        $this->post('/fazer-login', [
            'email' => 'teste@ifnmg.edu.br',
            'senha' => 'senha123',
        ])->assertRedirect('/');
    }

    // Login com senha incorreta retorna ao formulário com mensagem de erro
    // Usamos Referer: /login para simular o browser real (back() resolve para o referer)
    public function test_login_com_senha_invalida_retorna_para_login(): void
    {
        User::factory()->create(['email' => 'teste@ifnmg.edu.br']);

        $this->withHeader('Referer', 'http://localhost/login')
            ->post('/fazer-login', [
                'email' => 'teste@ifnmg.edu.br',
                'senha' => 'senha_errada',
            ])
            ->assertRedirect('/login')
            ->assertSessionHas('error');
    }

    // Logout encerra a sessão e redireciona para /login
    public function test_logout_encerra_sessao(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    }
}
