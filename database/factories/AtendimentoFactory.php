<?php

namespace Database\Factories;

use App\Models\Atendimento;
use App\Models\Paciente;
use App\Models\Profissional;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AtendimentoFactory extends Factory
{
    protected $model = Atendimento::class;

    public function definition(): array
    {
        return [
            'paciente_id'     => Paciente::factory(),
            'profissional_id' => Profissional::factory(),
            'criado_por_id'   => User::factory(),
            'status'          => 'aberto',
        ];
    }

    // Estado "fechado" para testes de atendimento encerrado
    public function fechado(): static
    {
        return $this->state(fn() => [
            'status'         => 'fechado',
            'fechado_por_id' => User::factory(),
            'fechado_em'     => now(),
        ]);
    }
}
