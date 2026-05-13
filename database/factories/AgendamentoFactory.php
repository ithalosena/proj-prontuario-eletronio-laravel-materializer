<?php

namespace Database\Factories;

use App\Models\Agendamento;
use App\Models\Paciente;
use App\Models\Profissional;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AgendamentoFactory extends Factory
{
    protected $model = Agendamento::class;

    public function definition(): array
    {
        return [
            'profissional_id'     => Profissional::factory(),
            'paciente_id'         => Paciente::factory(),
            'criado_por_id'       => User::factory(),
            'cancelado_por_id'    => null,
            'data_hora'           => now()->addDays(rand(1, 30)),
            'tipo'                => 'Consulta',
            'status'              => 'pendente',
            'observacao'          => null,
            'motivo_cancelamento' => null,
            'cancelado_em'        => null,
            'consulta_id'         => null,
        ];
    }

    // Estado: confirmado
    public function confirmado(): static
    {
        return $this->state(fn () => ['status' => 'confirmado']);
    }

    // Estado: cancelado (com motivo e data de cancelamento)
    public function cancelado(): static
    {
        return $this->state(fn (array $attr) => [
            'status'              => 'cancelado',
            'cancelado_por_id'    => $attr['criado_por_id'],
            'motivo_cancelamento' => 'Teste de cancelamento',
            'cancelado_em'        => now(),
        ]);
    }
}
