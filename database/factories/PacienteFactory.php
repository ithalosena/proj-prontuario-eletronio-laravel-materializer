<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PacienteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'         => User::factory(),
            'nome'            => fake()->name(),
            'contato'         => fake()->numerify('(##) #####-####'),
            'documento'       => fake()->unique()->numerify('###.###.###-##'),
            'data_nascimento' => fake()->dateTimeBetween('-50 years', '-18 years')->format('Y-m-d'),
            'sexo'            => fake()->randomElement(['M', 'F']),
            'endereco'        => fake()->address(),
            'matricula'       => fake()->unique()->numerify('IF######'),
            'curso'           => 'ADS',
        ];
    }
}
