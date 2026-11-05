<?php

namespace Database\Factories;

use App\Models\Especialidade;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => 'medico']),
            'especialidade_id' => Especialidade::inRandomOrder()->first()->id,
            'crm' => fake()->unique()->numerify('PE-####'),
        ];
    }
}