<?php

namespace Database\Seeders;

use App\Models\Paciente;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(EspecialidadeSeeder::class);

        User::factory()->create([
            'name' => 'Admin CliniGo',
            'email' => 'admin@clinigo.test',
            'role' => 'admin',
        ]);

        Paciente::factory()->count(30)->create();
    }
}