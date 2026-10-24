<?php

namespace Database\Factories;

use App\Models\Paciente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Paciente>
 */
class PacienteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        
    return [
        'nome' => fake()->name(),
        'cpf' => fake()->unique()->cpf(false),
        'data_nascimento' => fake()->dateTimeBetween('-90 years', '-1 year')->format('Y-m-d'),
        'telefone' => fake()->numerify('819########'),
    ];
    }
}
