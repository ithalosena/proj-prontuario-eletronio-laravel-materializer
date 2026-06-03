<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================
    // GET /perfil — exibe formulário
    // =========================================================

    public function test_usuario_autenticado_acessa_perfil(): void
    {
        $admin = $this->criarAdmin();

        $this->actingAs($admin)
             ->get('/perfil')
             ->assertOk()
             ->assertViewIs('content.pages.perfil')
             ->assertViewHas('user', $admin);
    }

    public function test_usuario_nao_autenticado_e_redirecionado_do_perfil(): void
    {
        $this->get('/perfil')->assertRedirect('/login');
    }

    // =========================================================
    // PUT /perfil — atualizar nome e e-mail
    // =========================================================

    public function test_atualizar_nome_e_email(): void
    {
        $user = $this->criarAdmin();

        $this->actingAs($user)
             ->put('/perfil', [
                 'name'  => 'Novo Nome',
                 'email' => 'novo@email.com',
             ])
             ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id'    => $user->id,
            'name'  => 'Novo Nome',
            'email' => 'novo@email.com',
        ]);
    }

    public function test_email_duplicado_e_rejeitado(): void
    {
        $user1 = $this->criarAdmin();
        [$user2] = $this->criarProfissionalUser();

        $this->actingAs($user1)
             ->put('/perfil', [
                 'name'  => $user1->name,
                 'email' => $user2->email, // e-mail de outro usuário
             ])
             ->assertSessionHasErrors('email');
    }

    // =========================================================
    // PUT /perfil — alterar senha
    // =========================================================

    public function test_alterar_senha_com_senha_atual_correta(): void
    {
        $user = $this->criarAdmin();
        // Factory usa bcrypt('password')
        $user->update(['password' => Hash::make('senha-atual')]);

        $this->actingAs($user)
             ->put('/perfil', [
                 'name'             => $user->name,
                 'email'            => $user->email,
                 'current_password' => 'senha-atual',
                 'new_password'     => 'nova-senha-123',
                 'new_password_confirmation' => 'nova-senha-123',
             ])
             ->assertRedirect()
             ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('nova-senha-123', $user->fresh()->password));
    }

    public function test_senha_atual_incorreta_retorna_erro(): void
    {
        $user = $this->criarAdmin();
        $user->update(['password' => Hash::make('senha-correta')]);

        $this->actingAs($user)
             ->put('/perfil', [
                 'name'             => $user->name,
                 'email'            => $user->email,
                 'current_password' => 'senha-errada',
                 'new_password'     => 'outra-senha-123',
                 'new_password_confirmation' => 'outra-senha-123',
             ])
             ->assertSessionHasErrors('current_password');
    }

    // =========================================================
    // POST /perfil/avatar — upload
    // =========================================================

    public function test_upload_de_avatar(): void
    {
        Storage::fake('public');
        $user = $this->criarAdmin();

        $arquivo = UploadedFile::fake()->image('foto.jpg', 100, 100);

        $this->actingAs($user)
             ->post('/perfil/avatar', ['avatar' => $arquivo])
             ->assertRedirect()
             ->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertNotNull($user->avatar);
        Storage::disk('public')->assertExists($user->avatar);
    }

    public function test_upload_de_arquivo_invalido_e_rejeitado(): void
    {
        Storage::fake('public');
        $user = $this->criarAdmin();

        $arquivo = UploadedFile::fake()->create('documento.pdf', 500, 'application/pdf');

        $this->actingAs($user)
             ->post('/perfil/avatar', ['avatar' => $arquivo])
             ->assertSessionHasErrors('avatar');
    }

    // =========================================================
    // DELETE /perfil/avatar/remover — remoção
    // =========================================================

    public function test_remover_avatar(): void
    {
        Storage::fake('public');
        $user = $this->criarAdmin();

        // Cria um arquivo falso e vincula ao usuário
        Storage::disk('public')->put('avatars/' . $user->id . '.jpg', 'conteudo');
        $user->update(['avatar' => 'avatars/' . $user->id . '.jpg']);

        $this->actingAs($user)
             ->delete('/perfil/avatar/remover')
             ->assertRedirect()
             ->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertNull($user->avatar);
        Storage::disk('public')->assertMissing('avatars/' . $user->id . '.jpg');
    }
}
