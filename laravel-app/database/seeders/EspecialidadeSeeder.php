<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Especialidade;

class EspecialidadeSeeder extends Seeder
{
    public function run(): void
    {
        $nomes = [
            'Clínica Geral',
            'Cardiologia',
            'Dermatologia',
            'Pediatria',
            'Ortopedia',
            'Ginecologia'
        ];

        foreach ($nomes as $nome) {
            Especialidade::firstOrCreate([
                'nome' => $nome
            ]);
        }
    }
}
