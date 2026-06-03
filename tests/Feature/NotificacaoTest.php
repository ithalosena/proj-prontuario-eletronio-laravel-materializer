<?php

namespace Tests\Feature;

use App\Models\Agendamento;
use App\Notifications\AgendamentoConfirmadoNotification;
use App\Notifications\AgendamentoCanceladoNotification;
use App\Notifications\NovoAgendamentoNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

// Sprint v0.8.5 — testes de notificações in-app via canal database
class NotificacaoTest extends TestCase
{
    use RefreshDatabase;

    // Confirmar agendamento deve disparar AgendamentoConfirmadoNotification para o paciente
    public function test_confirmar_agendamento_notifica_paciente(): void
    {
        Notification::fake();

        [$userProf, $profissional] = $this->criarProfissionalUser();
        [$userPaciente, $paciente] = $this->criarPacienteUser();

        $agendamento = Agendamento::factory()->create([
            'profissional_id' => $profissional->id,
            'paciente_id'     => $paciente->id,
            'status'          => 'pendente',
        ]);

        $this->actingAs($userProf)
            ->patch("/agendamentos/{$agendamento->id}/confirmar")
            ->assertRedirect();

        Notification::assertSentTo(
            $userPaciente,
            AgendamentoConfirmadoNotification::class
        );
    }

    // Cancelar agendamento deve disparar AgendamentoCanceladoNotification para a outra parte
    public function test_cancelar_agendamento_notifica_outra_parte(): void
    {
        Notification::fake();

        [$userProf, $profissional] = $this->criarProfissionalUser();
        [$userPaciente, $paciente] = $this->criarPacienteUser();

        $agendamento = Agendamento::factory()->create([
            'profissional_id' => $profissional->id,
            'paciente_id'     => $paciente->id,
            'status'          => 'pendente',
        ]);

        // Profissional cancela — paciente deve ser notificado
        $this->actingAs($userProf)
            ->patch("/agendamentos/{$agendamento->id}/cancelar", [
                'motivo_cancelamento' => 'Profissional indisponível',
            ])
            ->assertRedirect();

        Notification::assertSentTo(
            $userPaciente,
            AgendamentoCanceladoNotification::class
        );
    }

    // PATCH /notificacoes/{id}/ler deve marcar read_at e retornar redirect
    public function test_marcar_notificacao_como_lida(): void
    {
        [$userProf, $profissional] = $this->criarProfissionalUser();
        $paciente = $this->criarPacienteUser()[1];

        $agendamento = Agendamento::factory()->create([
            'profissional_id' => $profissional->id,
            'paciente_id'     => $paciente->id,
            'status'          => 'pendente',
        ]);

        // Cria a notificação real no banco (sem fake)
        $userProf->notify(new NovoAgendamentoNotification($agendamento));

        $notificacao = $userProf->unreadNotifications->first();
        $this->assertNotNull($notificacao, 'Notificação deve ter sido criada no banco.');
        $this->assertNull($notificacao->read_at, 'Notificação deve estar não lida.');

        $this->actingAs($userProf)
            ->patch("/notificacoes/{$notificacao->id}/ler")
            ->assertRedirect();

        $this->assertNotNull(
            $userProf->fresh()->notifications()->find($notificacao->id)?->read_at,
            'read_at deve estar preenchido após marcar como lida.'
        );
    }

    // GET /notificacoes deve retornar 301 redirecionando para home (página removida na v0.8.5-ux)
    public function test_rota_notificacoes_redireciona_para_home_com_301(): void
    {
        [$userProf] = $this->criarProfissionalUser();

        $this->actingAs($userProf)
            ->get('/notificacoes')
            ->assertStatus(301)
            ->assertRedirect('/');
    }
}
