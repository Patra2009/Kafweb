<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Menu;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function createSellableMenu(int $price = 20000, int $stock = 5): Menu
    {
        return Menu::factory()
            ->for(Category::factory())
            ->create(['price' => $price, 'stock' => $stock]);
    }

    public function test_customer_can_checkout_without_login(): void
    {
        $menu = $this->createSellableMenu();

        $this->postJson('/api/v1/orders', [
            'phone' => '081299999999',
            'name' => 'Andi Pelanggan',
            'table_number' => '5',
            'items' => [
                ['menu_id' => $menu->id, 'quantity' => 2, 'notes' => 'jangan pakai gula'],
            ],
        ])
            ->assertCreated()
            ->assertJsonStructure([
                'data' => ['id', 'tracking_code', 'total_price', 'order_status', 'items', 'customer'],
            ])
            ->assertJsonPath('data.order_status', 'pending')
            ->assertJsonPath('data.total_price', '40000.00')
            ->assertJsonPath('data.items.0.line_total', 40000)
            ->assertJsonPath('data.customer.name', 'Andi Pelanggan');

        $this->assertDatabaseHas(Customer::class, ['phone' => '081299999999']);
        $this->assertDatabaseHas(Order::class, [
            'total_price' => '40000.00',
            'table_number' => '5',
        ]);
        $this->assertSame(3, $menu->fresh()->stock);
    }

    public function test_price_is_taken_from_database_not_client_payload(): void
    {
        $menu = $this->createSellableMenu(price: 20000);

        $this->postJson('/api/v1/orders', [
            'items' => [
                ['menu_id' => $menu->id, 'quantity' => 2],
            ],
            'total_price' => '1.00',
        ])
            ->assertCreated()
            ->assertJsonPath('data.total_price', '40000.00');
    }

    public function test_rejects_quantity_beyond_remaining_stock(): void
    {
        $menu = $this->createSellableMenu(stock: 2);

        $this->postJson('/api/v1/orders', [
            'items' => [
                ['menu_id' => $menu->id, 'quantity' => 3],
            ],
        ])
            ->assertUnprocessable();

        $this->assertDatabaseCount(Order::class, 0);
        $this->assertSame(2, $menu->fresh()->stock);
    }

    public function test_rejects_unavailable_menu(): void
    {
        $menu = $this->createSellableMenu();
        $menu->update(['is_available' => false]);

        $this->postJson('/api/v1/orders', [
            'items' => [
                ['menu_id' => $menu->id, 'quantity' => 1],
            ],
        ])->assertUnprocessable();
    }

    public function test_rejects_oversell_split_across_two_lines(): void
    {
        $menu = $this->createSellableMenu(stock: 5);

        $this->postJson('/api/v1/orders', [
            'items' => [
                ['menu_id' => $menu->id, 'quantity' => 3],
                ['menu_id' => $menu->id, 'quantity' => 3],
            ],
        ])
            ->assertUnprocessable();

        $this->assertDatabaseCount(Order::class, 0);
    }

    public function test_allows_walkin_order_without_customer_data(): void
    {
        $menu = $this->createSellableMenu();

        $this->postJson('/api/v1/orders', [
            'items' => [
                ['menu_id' => $menu->id, 'quantity' => 1],
            ],
        ])
            ->assertCreated()
            ->assertJsonPath('data.customer', null);

        $this->assertDatabaseCount(Order::class, 1);
        $this->assertNull(Order::first()->customer_id);
    }

    public function test_validates_items_payload(): void
    {
        $this->postJson('/api/v1/orders', ['items' => []])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('items');

        $this->postJson('/api/v1/orders', [
            'items' => [
                ['menu_id' => 999999, 'quantity' => 1],
            ],
        ])->assertUnprocessable();
    }
}
