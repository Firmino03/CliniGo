<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Paciente;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(EspecialidadeSeeder::class);

        Paciente::factory()->count(30)->create();
    }
}
