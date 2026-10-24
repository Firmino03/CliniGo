<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EspecialidadeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $nomes = ['Clínica Geral', 'Cardiologia', 'Dermatologia', 'Pediatria', 'Ortopedia', 'Ginecologia'];

        foreach ($nomes as $nome) {
            Especialidade::firstOrCreate(['nome' => $nome]);
        }
    }
}
