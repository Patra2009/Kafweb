<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialReportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Place an order via the public checkout endpoint.
     */
    private function placeOrder(Menu $menu, int $quantity): int
    {
        return (int) $this->postJson('/api/v1/orders', [
            'items' => [
                ['menu_id' => $menu->id, 'quantity' => $quantity],
            ],
        ])->json('data.id');
    }

    /**
     * Pay an order as an acting kasir.
     */
    private function pay(int $orderId): void
    {
        $total = (float) Order::find($orderId)->total_price;

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->postJson("/api/v1/kasir/orders/{$orderId}/payment", [
                'method' => 'cash',
                'amount' => $total,
            ])->assertOk();
    }

    public function test_summary_only_counts_paid_revenue(): void
    {
        $menu = Menu::factory()->for(Category::factory())->create(['price' => 20000]);

        $paidId = $this->placeOrder($menu, 3);
        $unpaidId = $this->placeOrder($menu, 1);
        $cancelledId = $this->placeOrder($menu, 1);

        $this->pay($paidId);

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->patchJson("/api/v1/kasir/orders/{$cancelledId}/status", ['order_status' => 'cancelled'])
            ->assertOk();

        $this->assertDatabaseHas(Order::class, [
            'id' => $unpaidId,
            'payment_status' => 'pending',
        ]);

        $this->actingAs(User::factory()->admin()->create(), 'sanctum')
            ->getJson('/api/v1/admin/reports/summary')
            ->assertOk()
            ->assertJsonPath('data.revenue', 60000)
            ->assertJsonPath('data.paid_orders', 1)
            ->assertJsonPath('data.average_ticket', 60000)
            ->assertJsonPath('data.cancelled_orders', 1)
            ->assertJsonPath('data.active_orders', 2)
            ->assertJsonPath('data.unpaid_orders', 1);
    }

    public function test_daily_breakdown_groups_paid_orders_by_calendar_day(): void
    {
        $menu = Menu::factory()->for(Category::factory())->create(['price' => 25000]);

        $firstOrder = $this->placeOrder($menu, 1);
        $secondOrder = $this->placeOrder($menu, 2);

        $this->pay($firstOrder);
        $this->pay($secondOrder);

        $response = $this->actingAs(User::factory()->admin()->create(), 'sanctum')
            ->getJson('/api/v1/admin/reports/daily')
            ->assertOk();

        $rows = $response->json('data');
        $this->assertCount(1, $rows);
        $this->assertSame(2, (int) $rows[0]['orders_count']);
        $this->assertSame(75000.0, (float) $rows[0]['revenue']);
    }

    public function test_top_items_rank_by_quantity_sold(): void
    {
        $bestSeller = Menu::factory()->for(Category::factory())->create(['name' => 'Kopi Susu', 'price' => 20000]);
        $runnerUp = Menu::factory()->for(Category::factory())->create(['name' => 'Matcha Latte', 'price' => 26000]);

        $this->pay($this->placeOrder($bestSeller, 3));
        $this->pay($this->placeOrder($runnerUp, 1));

        // Pesanan belum dibayar tidak boleh masuk laporan
        $this->placeOrder($bestSeller, 10);

        $this->actingAs(User::factory()->admin()->create(), 'sanctum')
            ->getJson('/api/v1/admin/reports/top-items')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.menu_id', $bestSeller->id)
            ->assertJsonPath('data.1.menu_id', $runnerUp->id);
    }
}
