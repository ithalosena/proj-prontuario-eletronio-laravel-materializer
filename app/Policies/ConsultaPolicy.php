<?php

namespace App\Policies;

use App\Models\Consulta;
use App\Models\User;

// Regras de autorização para Consulta (DT-03 + v0.7.6).
// view(): profissional só vê consultas do próprio paciente (profissional_id); admin/coord/recep veem tudo.
// update()/delete(): somente o criador, e apenas com atendimento aberto.
class ConsultaPolicy
{
    // Qualquer profissional com acesso legítimo pode visualizar — mas profissional (nivel 3)
    // só vê consultas onde é o profissional responsável.
    public function view(User $user, Consulta $consulta): bool
    {
        // Admin (1), coordenador (2), recepcionista (4) veem qualquer consulta
        $nivel = $user->nivelAcesso();
        if ($nivel <= 2 || $nivel === 4) {
            return true;
        }

        // Profissional (3): apenas as próprias consultas
        return $user->profissional?->id === $consulta->profissional_id;
    }

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
