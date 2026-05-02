<?php

namespace Database\Factories;

use App\Models\Profissional;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfissionalFactory extends Factory
{
    protected $model = Profissional::class;

    public function definition(): array
    {
        return [
            'user_id'               => User::factory(),
            'nome'                  => fake()->name(),
            'contato'               => fake()->numerify('(##) #####-####'),
            'especialidade'         => fake()->randomElement(['Clínico Geral', 'Odontologia', 'Psicologia', 'Nutrição', 'Fisioterapia', 'Serviço Social']),
            'registro_profissional' => fake()->unique()->numerify('CRM/MG-######'),
        ];
    }
}
