<?php

namespace App\Policies;

use App\Models\Exame;
use App\Models\User;

// Regras de autoria para Exame (DT-03).
// Mesma lógica de ConsultaPolicy; o atendimento é acessado via exame → consulta → atendimento.
class ExamePolicy
{
    public function update(User $user, Exame $exame): bool
    {
        if ($user->nivelAcesso() <= 1) return true;
        if ($user->id !== $exame->criado_por_id) return false;
        $atendimento = optional($exame->consulta)->atendimento ?? null;
        if (is_null($atendimento)) return true;
        return $atendimento->isAberto();
    }

    public function delete(User $user, Exame $exame): bool
    {
        return $this->update($user, $exame);
    }
}
