<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class PaymentService
{
    /**
     * Record a payment and settle the order atomically.
     *
     * The paid amount may exceed the bill; the difference is the cash change
     * and it is returned to the cashier screen.
     *
     * @return array{payment: Payment, change: float}
     */
    public function record(User $cashier, Order $order, PaymentMethod $method, float $amount): array
    {
        return DB::transaction(function () use ($cashier, $order, $method, $amount): array {
            $order = Order::query()
                ->whereKey($order->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($order->payment_status === PaymentStatus::Paid) {
                throw new ConflictHttpException('Pesanan ini sudah dibayar sebelumnya.');
            }

            if ($order->order_status === OrderStatus::Cancelled) {
                throw new ConflictHttpException('Pesanan yang dibatalkan tidak dapat dibayar.');
            }

            if ($amount < (float) $order->total_price) {
                throw ValidationException::withMessages([
                    'amount' => 'Nominal bayar (Rp'.number_format($amount, 2).') kurang dari total tagihan (Rp'.number_format((float) $order->total_price, 2).').',
                ]);
            }

            $payment = $order->payments()->create([
                'cashier_id' => $cashier->id,
                'amount' => $amount,
                'method' => $method,
                'paid_at' => now(),
            ]);

            $order->update([
                'payment_status' => PaymentStatus::Paid,
                'paid_at' => now(),
            ]);

            return [
                'payment' => $payment,
                'change' => round($amount - (float) $order->total_price, 2),
            ];
        });
    }
}
