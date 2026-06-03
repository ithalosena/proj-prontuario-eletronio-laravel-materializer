<?php

namespace App\Notifications;

use App\Models\Agendamento;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AgendamentoCanceladoNotification extends Notification
{
    use Queueable;

    public function __construct(
        private Agendamento $agendamento,
        private User $canceladoPor
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $ag = $this->agendamento;
        $dataFormatada = $ag->data_hora->format('d/m/Y \à\s H:i');
        $motivo = $ag->motivo_cancelamento ?? 'Não informado';

        return [
            'agendamento_id' => $ag->id,
            'titulo'         => 'Agendamento cancelado',
            'mensagem'       => "O agendamento de {$dataFormatada} foi cancelado. Motivo: {$motivo}",
            'url'            => $notifiable->nivelAcesso() === 5
                ? '/meus-agendamentos'
                : "/agendamentos/{$ag->id}",
            'icone'          => 'mdi-calendar-remove-outline',
            'cor'            => 'danger',
        ];
    }
}
