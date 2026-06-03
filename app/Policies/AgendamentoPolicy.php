<?php

namespace App\Policies;

use App\Models\Agendamento;
use App\Models\User;

// Policy criada em v0.7.6 para corrigir IDOR (S-02).
// Profissional (nivel 3) só acessa agendamentos onde é o responsável.
// Admin, coordenador e recepcionista têm acesso irrestrito.
class AgendamentoPolicy
{
    // Regra base: admin (1), coordenador (2) e recepcionista (4) veem todos;
    // profissional (3) só vê os seus próprios.
    public function view(User $user, Agendamento $agendamento): bool
    {
        $nivel = $user->nivelAcesso();

        if ($nivel <= 2 || $nivel === 4) {
            return true;
        }

        // Profissional (3): apenas agendamentos onde é o profissional responsável
        return $user->profissional?->id === $agendamento->profissional_id;
    }

    // Confirmar e realizar seguem a mesma regra de visibilidade
    public function update(User $user, Agendamento $agendamento): bool
    {
        return $this->view($user, $agendamento);
    }

    // Cancelar — qualquer nível com acesso ao agendamento pode cancelar
    public function cancelar(User $user, Agendamento $agendamento): bool
    {
        return $this->view($user, $agendamento);
    }
}
