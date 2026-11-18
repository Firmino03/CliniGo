<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_com_credenciais_validas_retorna_token(): void
    {
        $user = User::factory()->create(['email' => 'admin@clinigo.test']);

        $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonStructure(['token', 'user']);
    }

    public function test_login_com_senha_errada_retorna_401(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'senha-errada',
        ])
            ->assertStatus(401)
            ->assertJson(['message' => 'Credenciais inválidas']);
    }

    public function test_login_sem_dados_retorna_422(): void
    {
        $this->postJson('/api/v1/login', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_rota_protegida_sem_token_retorna_401(): void
    {
        $this->getJson('/api/v1/me')->assertUnauthorized();
    }

    public function test_logout_remove_o_token(): void
    {
        $user = User::factory()->create();

        $token = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->json('token');

        $this->assertDatabaseCount('personal_access_tokens', 1);

        $this->withToken($token)
            ->postJson('/api/v1/logout')
            ->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}