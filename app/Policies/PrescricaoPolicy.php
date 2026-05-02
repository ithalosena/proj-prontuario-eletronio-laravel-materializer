<?php

namespace App\Policies;

use App\Models\Prescricao;
use App\Models\User;

// Regras de autoria para Prescricao (DT-03).
// Mesma lógica de ConsultaPolicy; o atendimento é acessado via prescricao → consulta → atendimento.
class PrescricaoPolicy
{
    public function update(User $user, Prescricao $prescricao): bool
    {
        if ($user->nivelAcesso() <= 1) return true;
        if ($user->id !== $prescricao->criado_por_id) return false;
        $atendimento = optional($prescricao->consulta)->atendimento ?? null;
        if (is_null($atendimento)) return true;
        return $atendimento->isAberto();
    }

    public function delete(User $user, Prescricao $prescricao): bool
    {
        return $this->update($user, $prescricao);
    }
}
