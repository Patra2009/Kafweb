<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_login_and_receive_sanctum_token(): void
    {
        User::factory()->admin()->create(['username' => 'owner', 'password' => 'secret123']);

        $this->postJson('/api/v1/auth/login', [
            'username' => 'owner',
            'password' => 'secret123',
        ])
            ->assertOk()
            ->assertJsonStructure([
                'token',
                'user' => ['id', 'name', 'username', 'role', 'role_label'],
            ]);
    }

    public function test_login_rejects_wrong_credentials(): void
    {
        User::factory()->create(['username' => 'kasir', 'password' => 'secret123']);

        $this->postJson('/api/v1/auth/login', [
            'username' => 'kasir',
            'password' => 'wrong-password',
        ])->assertUnauthorized();
    }

    public function test_login_requires_username_and_password(): void
    {
        $this->postJson('/api/v1/auth/login', ['username' => 'kasir'])
            ->assertUnprocessable();
    }

    public function test_rate_limiting_blocks_after_five_attempts(): void
    {
        User::factory()->create(['username' => 'kasir', 'password' => 'secret123']);

        foreach (range(1, 5) as $attempt) {
            $this->postJson('/api/v1/auth/login', [
                'username' => 'kasir',
                'password' => 'wrong-password-'.$attempt,
            ])->assertUnauthorized();
        }

        $this->postJson('/api/v1/auth/login', [
            'username' => 'kasir',
            'password' => 'wrong-password-6',
        ])->assertStatus(429);
    }

    public function test_kasir_is_forbidden_on_admin_endpoints(): void
    {
        $kasir = User::factory()->create();

        $this->actingAs($kasir, 'sanctum')
            ->getJson('/api/v1/admin/menus')
            ->assertForbidden();
    }

    public function test_guest_is_unauthorized_on_kasir_endpoints(): void
    {
        $this->getJson('/api/v1/kasir/orders')->assertUnauthorized();
    }

    public function test_web_session_reaches_dashboard_and_grants_api_access(): void
    {
        $kasir = User::factory()->create();

        $this->actingAs($kasir)
            ->get('/kasir/dashboard')
            ->assertOk();

        $this->actingAs($kasir)
            ->getJson('/api/v1/kasir/orders')
            ->assertOk();
    }

    public function test_admin_web_session_grants_admin_api_access(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk();

        $this->actingAs($admin)
            ->getJson('/api/v1/kasir/orders')
            ->assertOk();
    }
}
