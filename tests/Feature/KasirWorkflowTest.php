<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KasirWorkflowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Place an order through the public checkout endpoint.
     *
     * @return array{id: int, menu: Menu, total: float}
     */
    private function checkout(): array
    {
        $menu = Menu::factory()->for(Category::factory())->create();

        $orderId = (int) $this->postJson('/api/v1/orders', [
            'items' => [
                ['menu_id' => $menu->id, 'quantity' => 1],
            ],
        ])->json('data.id');

        return ['id' => $orderId, 'menu' => $menu, 'total' => (float) $menu->price];
    }

    public function test_pipeline_is_pending_processing_ready(): void
    {
        $order = $this->checkout();

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->patchJson("/api/v1/kasir/orders/{$order['id']}/status", ['order_status' => 'processing'])
            ->assertOk()
            ->assertJsonPath('data.order_status', 'processing');

        $this->patchJson("/api/v1/kasir/orders/{$order['id']}/status", ['order_status' => 'ready'])
            ->assertOk()
            ->assertJsonPath('data.order_status', 'ready');

        $this->assertSame(OrderStatus::Ready, Order::find($order['id'])->order_status);
    }

    public function test_backwards_transitions_are_rejected(): void
    {
        $order = $this->checkout();

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->patchJson("/api/v1/kasir/orders/{$order['id']}/status", ['order_status' => 'ready'])
            ->assertOk();

        $this->patchJson("/api/v1/kasir/orders/{$order['id']}/status", ['order_status' => 'processing'])
            ->assertConflict()
            ->assertJsonPath('message', "Status pesanan tidak dapat berubah dari 'ready' menjadi 'processing'.");
    }

    public function test_unpaid_order_cannot_be_completed(): void
    {
        $order = $this->checkout();

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->patchJson("/api/v1/kasir/orders/{$order['id']}/status", ['order_status' => 'ready'])
            ->assertOk();

        $this->patchJson("/api/v1/kasir/orders/{$order['id']}/status", ['order_status' => 'completed'])
            ->assertConflict()
            ->assertJsonPath('message', 'Pesanan harus dibayar terlebih dahulu sebelum ditandai selesai.');
    }

    public function test_paid_order_cannot_be_cancelled_from_the_app(): void
    {
        $order = $this->checkout();

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->postJson("/api/v1/kasir/orders/{$order['id']}/payment", [
                'method' => 'qris',
                'amount' => $order['total'],
            ])
            ->assertOk();

        $this->patchJson("/api/v1/kasir/orders/{$order['id']}/status", ['order_status' => 'cancelled'])
            ->assertConflict()
            ->assertJsonPath('message', 'Pesanan yang sudah dibayar tidak dapat dibatalkan lewat aplikasi; hubungi admin untuk proses refund.');
    }

    public function test_unpaid_order_may_be_cancelled(): void
    {
        $order = $this->checkout();

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->patchJson("/api/v1/kasir/orders/{$order['id']}/status", ['order_status' => 'cancelled'])
            ->assertOk()
            ->assertJsonPath('data.order_status', 'cancelled');

        $this->assertSame(OrderStatus::Cancelled, Order::find($order['id'])->order_status);
    }

    public function test_dashboard_shows_active_orders_after_latest(): void
    {
        $first = $this->checkout();
        $second = $this->checkout();

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->patchJson("/api/v1/kasir/orders/{$second['id']}/status", ['order_status' => 'processing'])
            ->assertOk();

        Order::factory()->paid()->create([
            'order_status' => OrderStatus::Completed,
            'payment_status' => PaymentStatus::Paid,
        ]);
        Order::factory()->create(['order_status' => OrderStatus::Cancelled]);

        $response = $this->actingAs(User::factory()->create(), 'sanctum')
            ->getJson('/api/v1/kasir/orders')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->assertSame([$second['id'], $first['id']], collect($response->json('data'))->pluck('id')->all());

        $statuses = collect($response->json('data'))->pluck('order_status')->unique()->sort()->values()->all();
        $this->assertSame(['pending', 'processing'], $statuses);
    }

    public function test_dashboard_supports_status_and_date_filters(): void
    {
        $order = $this->checkout();

        Order::factory()->paid()->create([
            'order_status' => OrderStatus::Completed,
            'payment_status' => PaymentStatus::Paid,
        ]);

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->getJson('/api/v1/kasir/orders?order_status=pending')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $order['id']);

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->getJson('/api/v1/kasir/orders?payment_status=pending')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.payment_status', PaymentStatus::Pending->value);
    }

    public function test_kasir_may_place_a_walkin_order_from_the_counter(): void
    {
        $menu = Menu::factory()->for(Category::factory())->create();

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->postJson('/api/v1/kasir/orders', [
                'table_number' => '7',
                'items' => [
                    ['menu_id' => $menu->id, 'quantity' => 2],
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.table_number', '7')
            ->assertJsonPath('data.total_price', number_format((float) $menu->price * 2, 2, '.', ''));
    }
}
