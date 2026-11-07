<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Menu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Menu>
 */
class MenuFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 5000, 50000),
            'stock' => fake()->numberBetween(10, 100),
            'image_url' => null,
            'is_available' => true,
        ];
    }

    /**
     * Indicate that the menu is out of stock.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (): array => [
            'stock' => 0,
        ]);
    }

    /**
     * Indicate that the menu is hidden/locked by admin.
     */
    public function unavailable(): static
    {
        return $this->state(fn (): array => [
            'is_available' => false,
        ]);
    }
}
