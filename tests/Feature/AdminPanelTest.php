<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_categories(): void
    {
        $admin = User::factory()->admin()->create();

        $categoryId = (int) $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/admin/categories', ['name' => 'Kopi'])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Kopi')
            ->json('data.id');

        $this->postJson('/api/v1/admin/categories', ['name' => 'Kopi'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');

        $this->patchJson("/api/v1/admin/categories/{$categoryId}", ['name' => 'Minuman Kopi'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Minuman Kopi');

        $menu = Menu::factory()->create(['category_id' => $categoryId]);

        $this->deleteJson("/api/v1/admin/categories/{$categoryId}")->assertConflict();
        $this->assertNotNull(Menu::find($menu->id));

        $emptyCategory = Category::factory()->create();

        $this->deleteJson("/api/v1/admin/categories/{$emptyCategory->id}")->assertOk();
        $this->assertDatabaseMissing(Category::class, ['id' => $emptyCategory->id]);
    }

    public function test_admin_can_manage_menus(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $menuId = (int) $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/admin/menus', [
                'category_id' => $category->id,
                'name' => 'Espresso Macchiato',
                'description' => 'Duo shot espresso dengan sedikit susu.',
                'price' => 23000,
                'stock' => 50,
            ])
            ->assertCreated()
            ->assertJsonPath('data.category.name', $category->name)
            ->json('data.id');

        $this->patchJson("/api/v1/admin/menus/{$menuId}", [
            'price' => 25000,
            'stock' => 48,
        ])->assertOk()
            ->assertJsonPath('data.price', '25000.00')
            ->assertJsonPath('data.stock', 48);

        $this->deleteJson("/api/v1/admin/menus/{$menuId}")->assertOk();

        $referencedMenu = Menu::factory()->create(['category_id' => $category->id, 'price' => 10000]);

        OrderItem::factory()->create([
            'menu_id' => $referencedMenu->id,
            'quantity' => 1,
            'price' => $referencedMenu->price,
        ]);

        $this->deleteJson("/api/v1/admin/menus/{$referencedMenu->id}")->assertConflict();
        $this->assertNotNull(Menu::find($referencedMenu->id));
    }

    public function test_admin_can_manage_staff(): void
    {
        $admin = User::factory()->admin()->create();

        $staffId = (int) $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/admin/staff', [
                'name' => 'Kasir Baru',
                'username' => 'kasir_baru',
                'password' => 'rahasia123',
                'role' => 'kasir',
            ])
            ->assertCreated()
            ->assertJsonPath('data.role', UserRole::Kasir->value)
            ->json('data.id');

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/admin/staff', [
                'name' => 'Duplicate',
                'username' => 'kasir_baru',
                'password' => 'rahasia123',
                'role' => 'kasir',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('username');

        $this->patchJson("/api/v1/admin/staff/{$staffId}", ['role' => 'admin'])
            ->assertOk()
            ->assertJsonPath('data.role', UserRole::Admin->value);

        $this->deleteJson("/api/v1/admin/staff/{$staffId}")->assertOk();
        $this->assertDatabaseMissing(User::class, ['id' => $staffId]);
    }

    public function test_last_admin_is_always_protected(): void
    {
        $admin = User::factory()->admin()->create();
        $secondAdmin = User::factory()->admin()->create();

        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/v1/admin/staff/{$secondAdmin->id}")
            ->assertOk();

        // Admin terakhir tidak boleh terhapus (guard self + last-admin).
        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/v1/admin/staff/{$admin->id}")
            ->assertConflict();
    }

    public function test_staff_index_can_filter_by_role(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['name' => 'Kasir Aktif']);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/admin/staff?role=kasir')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Kasir Aktif');
    }

    public function test_kasir_is_forbidden_to_manage_catalogue(): void
    {
        $kasir = User::factory()->create();
        $category = Category::factory()->create();

        $this->actingAs($kasir, 'sanctum')
            ->postJson('/api/v1/admin/menus', [
                'category_id' => $category->id,
                'name' => 'Menu Rahasia',
                'price' => 1000,
                'stock' => 10,
            ])
            ->assertForbidden();
    }

    public function test_customers_with_order_history_are_protected(): void
    {
        $admin = User::factory()->admin()->create();

        $customerId = (int) $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/admin/customers', [
                'name' => 'Rina Melati',
                'phone' => '081200001111',
            ])
            ->assertCreated()
            ->json('data.id');

        Order::factory()->create(['customer_id' => $customerId]);

        $this->deleteJson("/api/v1/admin/customers/{$customerId}")->assertConflict();

        $this->patchJson("/api/v1/admin/customers/{$customerId}", ['name' => 'Rina M. Updated'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Rina M. Updated');
    }
}
