<?php

namespace App\Notifications;

use App\Models\Agendamento;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

// Disparada ao profissional confirmar um agendamento — destinatário: paciente
class AgendamentoConfirmadoNotification extends Notification
{
    use Queueable;

    public function __construct(private Agendamento $agendamento) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $ag = $this->agendamento;

        return [
            'agendamento_id' => $ag->id,
            'titulo'         => 'Agendamento confirmado',
            'mensagem'       => "Sua consulta com {$ag->profissional->nome} em " .
                                $ag->data_hora->format('d/m/Y') . ' às ' .
                                $ag->data_hora->format('H:i') . ' foi confirmada.',
            'url'            => '/meus-agendamentos',
            'icone'          => 'mdi-calendar-check-outline',
            'cor'            => 'success',
        ];
    }
}
