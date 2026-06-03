<?php

namespace App\Notifications;

use App\Models\Agendamento;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NovoAgendamentoNotification extends Notification
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
        $dataFormatada = $ag->data_hora->format('d/m/Y \à\s H:i');

        return [
            'agendamento_id' => $ag->id,
            'titulo'         => 'Novo agendamento',
            'mensagem'       => "Novo agendamento de {$ag->paciente->nome} para {$dataFormatada} ({$ag->tipo}).",
            'url'            => "/agendamentos/{$ag->id}",
            'icone'          => 'mdi-calendar-plus',
            'cor'            => 'primary',
        ];
    }
}
