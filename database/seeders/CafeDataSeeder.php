<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Menu;
use Illuminate\Database\Seeder;

class CafeDataSeeder extends Seeder
{
    /**
     * Menu catalogue published on the customer menu. Setiap menu memakai foto
     * makanan/minuman sungguhan (unsplash/pexels) agar katalog terasa menggugah selera.
     *
     * @return array<string, array<int, array{name: string, description: string, price: float, stock: int, image: string}>>
     */
    private function catalogue(): array
    {
        return [
            'Kopi' => [
                ['name' => 'Kopi Susu Gula Aren', 'description' => 'Espresso, milk, dan gula aren cair khas KafWeb.', 'price' => 22000.0, 'stock' => 100, 'image_id' => ['type' => 'unsplash', 'id' => '1512568400610-62da28bc8a13']],
                ['name' => 'Americano', 'description' => 'Espresso murni dengan air mineral panas.', 'price' => 18000.0, 'stock' => 100, 'image_id' => ['type' => 'unsplash', 'id' => '1514432324607-a09d9b4aefdd']],
                ['name' => 'Cappuccino', 'description' => 'Espresso dengan microfoam susu klasik.', 'price' => 25000.0, 'stock' => 80, 'image_id' => ['type' => 'unsplash', 'id' => '1541167760496-1628856ab772']],
                ['name' => 'Espresso', 'description' => 'Single shot arabika house blend.', 'price' => 14000.0, 'stock' => 100, 'image_id' => ['type' => 'unsplash', 'id' => '1510707577719-ae7c14805e3a']],
            ],
            'Non-Kopi' => [
                ['name' => 'Matcha Latte', 'description' => 'Matcha ceremonial grade dengan susu segar.', 'price' => 26000.0, 'stock' => 50, 'image_id' => ['type' => 'unsplash', 'id' => '1515823064-d6e0c04616a7']],
                ['name' => 'Cokelat Panas', 'description' => 'Dark chocolate dipanaskan dengan steamed milk.', 'price' => 20000.0, 'stock' => 50, 'image_id' => ['type' => 'unsplash', 'id' => '1497515114629-f71d768fd07c']],
                ['name' => 'Teh Tarik', 'description' => 'Teh susu sariwangi dengan foam lembut.', 'price' => 17000.0, 'stock' => 60, 'image_id' => ['type' => 'unsplash', 'id' => '1571934811356-5cc061b6821f']],
                ['name' => 'Air Mineral', 'description' => 'Botol air mineral 600 ml dingin.', 'price' => 8000.0, 'stock' => 200, 'image_id' => ['type' => 'unsplash', 'id' => '1548839140-29a749e1cf4d']],
            ],
            'Makanan' => [
                ['name' => 'Nasi Goreng Kafweb', 'description' => 'Nasi goreng spesial dengan telur dan ayam suwiri.', 'price' => 30000.0, 'stock' => 40, 'image_id' => ['type' => 'unsplash', 'id' => '1603133872878-684f208fb84b']],
                ['name' => 'Mie Goreng', 'description' => 'Mie goreng telur dengan sayuran segar.', 'price' => 28000.0, 'stock' => 40, 'image_id' => ['type' => 'unsplash', 'id' => '1552611052-33e04de081de']],
                ['name' => 'Roti Bakar Madu', 'description' => 'Roti bakar honey toast disiram madu, pisang, dan blueberry segar.', 'price' => 22000.0, 'stock' => 30, 'image_id' => ['type' => 'unsplash', 'id' => '1484723091739-30a097e8f929']],
            ],
            'Camilan' => [
                ['name' => 'Pisang Goreng', 'description' => 'Pisang goreng crispy khidmat dengan taburan gula.', 'price' => 15000.0, 'stock' => 25, 'image_id' => ['type' => 'pexels', 'id' => '37048319']],
                ['name' => 'French Fries', 'description' => 'Kentang goreng gurih dengan saus sambal.', 'price' => 18000.0, 'stock' => 35, 'image_id' => ['type' => 'unsplash', 'id' => '1573080496219-bb080dd4f877']],
                ['name' => 'Croissant Butter', 'description' => 'Croissant renyah dengan aroma butter premium.', 'price' => 19000.0, 'stock' => 20, 'image_id' => ['type' => 'unsplash', 'id' => '1555507036-ab1f4038808a']],
            ],
        ];
    }

    /**
     * Seed the cafe categories, menus, and sample customers.
     */
    public function run(): void
    {
        $catalogue = $this->catalogue();

        $categoryNames = array_keys($catalogue);
        $menuNames = collect($catalogue)->flatten(1)->pluck('name');
        $categoryIds = Category::whereIn('name', $categoryNames)->pluck('id');

        // Singkirkan menu lama pada katalog demo yang tidak lagi ada (misal hasil rename seed).
        Menu::query()
            ->where(function ($query) use ($categoryIds, $menuNames): void {
                $query->whereIntegerNotInRaw('category_id', $categoryIds)
                    ->orWhere(function ($mixed) use ($categoryIds, $menuNames): void {
                        $mixed->whereIn('category_id', $categoryIds)->whereNotIn('name', $menuNames);
                    });
            })
            ->delete();

        foreach ($catalogue as $categoryName => $menus) {
            $category = Category::updateOrCreate(attributes: ['name' => $categoryName]);

            foreach ($menus as $menu) {
                $imageUrl = match ($menu['image_id']['type']) {
                    'unsplash' => self::unsplash($menu['image_id']['id']),
                    'pexels' => self::pexels($menu['image_id']['id']),
                    default => null,
                };

                Menu::updateOrCreate(
                    attributes: ['name' => $menu['name']],
                    values: [
                        'category_id' => $category->id,
                        'description' => $menu['description'],
                        'price' => $menu['price'],
                        'stock' => $menu['stock'],
                        'image_url' => $imageUrl,
                        'is_available' => true,
                    ],
                );
            }
        }

        $customers = [
            ['name' => 'Budi Santoso', 'phone' => '081234567890'],
            ['name' => 'Siti Aminah', 'phone' => '082345678901'],
            ['name' => 'Andi Wijaya', 'phone' => '083456789012'],
        ];

        foreach ($customers as $customer) {
            Customer::updateOrCreate(attributes: $customer);
        }
    }

    /**
     * Bangun URL foto Unsplash yang sudah dioptimasi (crop 640x400).
     */
    private static function unsplash(string $photoId): string
    {
        return 'https://images.unsplash.com/photo-'.$photoId.'?auto=format&fit=crop&w=640&h=400&q=80';
    }

    /**
     * Bangun URL foto Pexels yang sudah dioptimasi (crop 640x400).
     */
    private static function pexels(string $photoId): string
    {
        return 'https://images.pexels.com/photos/'.$photoId.'/pexels-photo-'.$photoId.'.jpeg?auto=compress&cs=tinysrgb&w=640&h=400&fit=crop';
    }
}
