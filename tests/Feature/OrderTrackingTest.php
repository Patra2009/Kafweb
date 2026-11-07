<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTrackingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Place an order through the public checkout endpoint.
     *
     * @return array{id: int, tracking_code: string}
     */
    private function checkout(): array
    {
        $menu = Menu::factory()->for(Category::factory())->create();

        $order = $this->postJson('/api/v1/orders', [
            'phone' => '081211112222',
            'table_number' => '3',
            'items' => [
                ['menu_id' => $menu->id, 'quantity' => 1],
            ],
        ])
            ->assertCreated()
            ->json('data');

        return ['id' => (int) $order['id'], 'tracking_code' => $order['tracking_code']];
    }

    public function test_customer_polls_status_using_tracking_code(): void
    {
        $order = $this->checkout();

        $this->getJson("/api/v1/orders/{$order['tracking_code']}/status")
            ->assertOk()
            ->assertJsonPath('data.order_status', 'pending')
            ->assertJsonPath('data.order_status_label', 'Menunggu Konfirmasi')
            ->assertJsonPath('data.payment_status', 'pending');
    }

    public function test_tracking_reflects_kasir_updates(): void
    {
        $order = $this->checkout();

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->patchJson("/api/v1/kasir/orders/{$order['id']}/status", ['order_status' => 'processing'])
            ->assertOk()
            ->assertJsonPath('data.order_status', 'processing');

        $this->getJson("/api/v1/orders/{$order['tracking_code']}/status")
            ->assertOk()
            ->assertJsonPath('data.order_status', 'processing');
    }

    public function test_unknown_tracking_code_returns_404(): void
    {
        $this->getJson('/api/v1/orders/answered-nope/status')->assertNotFound();
        $this->getJson('/api/v1/orders/99999/status')->assertNotFound();
    }
}
