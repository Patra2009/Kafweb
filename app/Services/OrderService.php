<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Customer;
use App\Models\Menu;
use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class OrderService
{
    /**
     * Create an order inside one database transaction.
     *
     * Stock rows are locked with SELECT ... FOR UPDATE so two cashiers
     * checking out the same menu simultaneously can never oversell it.
     * Prices are always read from the database, never trusted from the client.
     *
     * @param  array{customer_id?: int|null, phone?: string|null, name?: string|null, table_number?: string|null, items: array<int, array{menu_id: int, quantity: int, notes?: string|null}>}  $data
     */
    public function create(array $data): Order
    {
        return DB::transaction(function () use ($data): Order {
            $quantities = $this->consolidateItems($data['items']);

            /** @var Collection<int, Menu> $menus */
            $menus = Menu::query()
                ->whereIn('id', $quantities->keys())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $totalPrice = 0.0;

            foreach ($quantities as $menuId => $quantity) {
                $menu = $menus->get($menuId);

                if ($menu === null) {
                    throw ValidationException::withMessages([
                        'items' => 'Menu tidak ditemukan atau sudah tidak tersedia.',
                    ]);
                }

                if ($menu->is_available === false) {
                    throw ValidationException::withMessages([
                        'items' => "Menu {$menu->name} sedang tidak tersedia.",
                    ]);
                }

                if ($menu->stock < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => "Stok menu {$menu->name} tidak cukup (sisa {$menu->stock}).",
                    ]);
                }

                $totalPrice += $quantity * (float) $menu->price;
                $menu->decrement('stock', $quantity);
            }

            $order = Order::create([
                'customer_id' => $this->resolveCustomer($data)?->id,
                'table_number' => $data['table_number'] ?? null,
                'total_price' => round($totalPrice, 2),
                'payment_status' => PaymentStatus::Pending,
                'order_status' => OrderStatus::Pending,
                'tracking_code' => $this->uniqueTrackingCode(),
            ]);

            $order->items()->createMany(
                collect($data['items'])
                    ->map(fn (array $item): array => [
                        'menu_id' => $item['menu_id'],
                        'quantity' => $item['quantity'],
                        'price' => $menus->get($item['menu_id'])->price,
                        'notes' => $item['notes'] ?? null,
                    ])
                    ->all(),
            );

            return $order->load('items.menu', 'customer');
        });
    }

    /**
     * Move an order to its next lifecycle status.
     *
     * Transitions follow the pipeline: pending -> processing -> ready -> completed.
     * Cancellation is always allowed while unpaid; completion requires payment.
     */
    public function changeStatus(Order $order, OrderStatus $newStatus): Order
    {
        $currentStatus = $order->order_status;

        if (! $currentStatus->canTransitionTo($newStatus)) {
            throw new ConflictHttpException(
                "Status pesanan tidak dapat berubah dari '{$currentStatus->value}' menjadi '{$newStatus->value}'.",
            );
        }

        if ($newStatus === OrderStatus::Cancelled && $order->payment_status === PaymentStatus::Paid) {
            throw new ConflictHttpException(
                'Pesanan yang sudah dibayar tidak dapat dibatalkan lewat aplikasi; hubungi admin untuk proses refund.',
            );
        }

        if ($newStatus === OrderStatus::Completed && $order->payment_status !== PaymentStatus::Paid) {
            throw new ConflictHttpException('Pesanan harus dibayar terlebih dahulu sebelum ditandai selesai.');
        }

        $order->order_status = $newStatus;
        $order->save();

        return $order->load('items.menu', 'customer');
    }

    /**
     * Sum quantities of repeated menu ids so stock checks
     * happen against the combined amount on one request.
     *
     * @param  array<int, array{menu_id: int, quantity: int}>  $items
     * @return Collection<int, int> menu id => total quantity
     */
    private function consolidateItems(array $items): Collection
    {
        return collect($items)
            ->groupBy('menu_id')
            ->map(fn (Collection $lines): int => (int) $lines->sum('quantity'));
    }

    /**
     * Find existing customer by id, or lazily register one by phone number.
     *
     * @param  array{customer_id?: int|null, phone?: string|null, name?: string|null}  $data
     */
    private function resolveCustomer(array $data): ?Customer
    {
        if (! empty($data['customer_id'])) {
            return Customer::find($data['customer_id']);
        }

        if (! empty($data['phone'])) {
            return Customer::firstOrCreate(
                attributes: ['phone' => $data['phone']],
                values: ['name' => $data['name'] ?? 'Pelanggan KafWeb'],
            );
        }

        return null;
    }

    /**
     * Generate a collision-safe tracking token for status polling.
     *
     * @throws \RuntimeException if unable to generate unique code after max attempts
     */
    private function uniqueTrackingCode(): string
    {
        $maxAttempts = 10;
        $attempt = 0;

        do {
            $code = Str::upper(Str::random(8));
            $attempt++;

            if ($attempt >= $maxAttempts) {
                throw new \RuntimeException('Unable to generate unique tracking code after '.$maxAttempts.' attempts.');
            }
        } while (Order::query()->where('tracking_code', $code)->exists());

        return $code;
    }
}
