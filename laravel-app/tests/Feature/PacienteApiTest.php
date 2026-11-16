<?php

namespace Tests\Feature;

use App\Models\Consulta;
use App\Models\Especialidade;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PacienteApiTest extends TestCase
{
    use RefreshDatabase;

    private function autenticar(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'admin']));
    }

    private function dadosValidos(array $extra = []): array
    {
        return array_merge([
            'nome' => 'Maria Souza',
            'cpf' => '12345678901',
            'data_nascimento' => '1990-05-10',
            'telefone' => '81999999999',
        ], $extra);
    }

    public function test_visitante_nao_acessa_pacientes(): void
    {
        $this->getJson('/api/v1/pacientes')->assertUnauthorized();
    }

    public function test_lista_pacientes_paginados(): void
    {
        $this->autenticar();
        Paciente::factory()->count(3)->create();

        $this->getJson('/api/v1/pacientes')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure(['data', 'links', 'meta']);
    }

    public function test_busca_pacientes_por_nome(): void
    {
        $this->autenticar();
        Paciente::factory()->create(['nome' => 'Maria Souza']);
        Paciente::factory()->create(['nome' => 'João Lima']);

        $this->getJson('/api/v1/pacientes?busca=Maria')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nome', 'Maria Souza');
    }

    public function test_cria_paciente(): void
    {
        $this->autenticar();

        $this->postJson('/api/v1/pacientes', $this->dadosValidos())
            ->assertCreated()
            ->assertJsonPath('data.nome', 'Maria Souza');

        $this->assertDatabaseHas('pacientes', ['cpf' => '12345678901']);
    }

    public function test_criar_paciente_sem_dados_retorna_422(): void
    {
        $this->autenticar();

        $this->postJson('/api/v1/pacientes', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['nome', 'data_nascimento', 'telefone']);
    }

    public function test_nao_permite_cpf_duplicado(): void
    {
        $this->autenticar();
        Paciente::factory()->create(['cpf' => '12345678901']);

        $this->postJson('/api/v1/pacientes', $this->dadosValidos())
            ->assertStatus(422)
            ->assertJsonValidationErrors(['cpf']);
    }

    public function test_mostra_um_paciente(): void
    {
        $this->autenticar();
        $paciente = Paciente::factory()->create();

        $this->getJson("/api/v1/pacientes/{$paciente->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $paciente->id);
    }

    public function test_atualiza_paciente_mantendo_o_proprio_cpf(): void
    {
        $this->autenticar();
        $paciente = Paciente::factory()->create(['cpf' => '12345678901']);

        $this->putJson("/api/v1/pacientes/{$paciente->id}", [
            'nome' => 'Nome Atualizado',
            'cpf' => '12345678901',
        ])
            ->assertOk()
            ->assertJsonPath('data.nome', 'Nome Atualizado');

        $this->assertDatabaseHas('pacientes', ['id' => $paciente->id, 'nome' => 'Nome Atualizado']);
    }

    public function test_exclui_paciente_sem_consultas(): void
    {
        $this->autenticar();
        $paciente = Paciente::factory()->create();

        $this->deleteJson("/api/v1/pacientes/{$paciente->id}")->assertNoContent();

        $this->assertDatabaseMissing('pacientes', ['id' => $paciente->id]);
    }

    public function test_nao_exclui_paciente_com_consultas(): void
    {
        $this->autenticar();
        Especialidade::create(['nome' => 'Cardiologia']);
        $medico = Medico::factory()->create();
        $paciente = Paciente::factory()->create();

        Consulta::create([
            'paciente_id' => $paciente->id,
            'medico_id' => $medico->id,
            'inicio' => now()->addDay(),
            'fim' => now()->addDay()->addHour(),
            'status' => 'agendada',
        ]);

        $this->deleteJson("/api/v1/pacientes/{$paciente->id}")->assertStatus(409);

        $this->assertDatabaseHas('pacientes', ['id' => $paciente->id]);
    }
}