<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'table_number' => fake()->optional()->numberBetween(1, 20),
            'total_price' => 0,
            'tracking_code' => Str::upper(Str::random(8)),
            'payment_status' => PaymentStatus::Pending,
            'order_status' => OrderStatus::Pending,
            'paid_at' => null,
        ];
    }

    /**
     * Indicate that the order has been paid.
     */
    public function paid(): static
    {
        return $this->state(fn (): array => [
            'payment_status' => PaymentStatus::Paid,
            'paid_at' => now(),
        ]);
    }
}
