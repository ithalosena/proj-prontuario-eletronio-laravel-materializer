<?php

namespace Database\Factories;

use App\Models\Consulta;
use App\Models\Paciente;
use App\Models\Profissional;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConsultaFactory extends Factory
{
    protected $model = Consulta::class;

    public function definition(): array
    {
        return [
            'profissional_id' => Profissional::factory(),
            'paciente_id'     => Paciente::factory(),
            'atendimento_id'  => null,
            'criado_por_id'   => null,
            'data_hora'       => now()->subDay(),
            'tipo'            => 'Clínico Geral',
            'queixa'          => fake()->sentence(),
            'anamnese'        => null,
            'diagnostico'     => null,
            'conduta'         => null,
        ];
    }
}
