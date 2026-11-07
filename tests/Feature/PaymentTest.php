<?php

namespace Tests\Feature;

use App\Enums\PaymentStatus;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Checkout order helper.
     *
     * @return array{id: int, total: float}
     */
    private function checkout(): array
    {
        $menu = Menu::factory()->for(Category::factory())->create(['price' => 20000]);

        $order = $this->postJson('/api/v1/orders', [
            'items' => [
                ['menu_id' => $menu->id, 'quantity' => 2],
            ],
        ])->json('data');

        return ['id' => (int) $order['id'], 'total' => 40000.0];
    }

    public function test_payment_settles_the_order(): void
    {
        $order = $this->checkout();

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->postJson("/api/v1/kasir/orders/{$order['id']}/payment", [
                'method' => 'cash',
                'amount' => $order['total'],
            ])
            ->assertOk()
            ->assertJsonPath('data.method', 'cash')
            ->assertJsonPath('change', 0)
            ->assertJsonPath('data.cashier_name', fn (string $name): bool => $name !== '');

        $paidOrder = Order::find($order['id']);
        $this->assertSame(PaymentStatus::Paid, $paidOrder->payment_status);
        $this->assertNotNull($paidOrder->paid_at);
        $this->assertDatabaseHas(Payment::class, [
            'order_id' => $order['id'],
            'amount' => '40000.00',
            'method' => 'cash',
        ]);
    }

    public function test_change_is_returned_for_larger_banknotes(): void
    {
        $order = $this->checkout();

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->postJson("/api/v1/kasir/orders/{$order['id']}/payment", [
                'method' => 'cash',
                'amount' => 50000,
            ])
            ->assertOk()
            ->assertJsonPath('change', 10000);
    }

    public function test_underpayment_is_rejected(): void
    {
        $order = $this->checkout();

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->postJson("/api/v1/kasir/orders/{$order['id']}/payment", [
                'method' => 'cash',
                'amount' => 1000,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('amount');

        $this->assertFalse(Order::find($order['id'])->payment_status === PaymentStatus::Paid);
    }

    public function test_double_payment_is_rejected(): void
    {
        $order = $this->checkout();

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->postJson("/api/v1/kasir/orders/{$order['id']}/payment", [
                'method' => 'cash',
                'amount' => $order['total'],
            ])->assertOk();

        $this->postJson("/api/v1/kasir/orders/{$order['id']}/payment", [
            'method' => 'cash',
            'amount' => $order['total'],
        ])->assertConflict();
    }

    public function test_cancelled_order_cannot_be_paid(): void
    {
        $order = $this->checkout();

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->patchJson("/api/v1/kasir/orders/{$order['id']}/status", ['order_status' => 'cancelled'])
            ->assertOk();

        $this->postJson("/api/v1/kasir/orders/{$order['id']}/payment", [
            'method' => 'cash',
            'amount' => $order['total'],
        ])->assertConflict();

        $this->assertDatabaseEmpty(Payment::class);
    }

    public function test_invalid_payment_method_is_rejected(): void
    {
        $order = $this->checkout();

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->postJson("/api/v1/kasir/orders/{$order['id']}/payment", [
                'method' => 'bitcoin',
                'amount' => $order['total'],
            ])->assertUnprocessable()
            ->assertJsonValidationErrors('method');
    }
}
