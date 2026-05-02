<?php

namespace App\Policies;

use App\Models\Consulta;
use App\Models\User;

// Regras de autoria para Consulta (DT-03).
// Admin (nivel <= 1) sempre pode; outros: somente o criador, e apenas se o atendimento estiver aberto.
class ConsultaPolicy
{
    public function update(User $user, Consulta $consulta): bool
    {
        if ($user->nivelAcesso() <= 1) return true;
        if ($user->id !== $consulta->criado_por_id) return false;
        $atendimento = $consulta->atendimento ?? null;
        if (is_null($atendimento)) return true;
        return $atendimento->isAberto();
    }

    public function delete(User $user, Consulta $consulta): bool
    {
        return $this->update($user, $consulta);
    }
}
