<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class CustomerOrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    /**
     * Checkout performed by the customer (no login required).
     *
     * The order is created by OrderService inside a transaction, with server
     * side price and stock validation, and the response includes a
     * tracking_code used for status polling.
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->create($request->orderData());

        return (new OrderResource($order))->response()->setStatusCode(201);
    }

    /**
     * Real-time order tracking that customers poll using their tracking code,
     * so the sequential order id is never exposed.
     */
    public function track(string $trackingCode): JsonResponse
    {
        $order = Order::query()
            ->where('tracking_code', strtoupper($trackingCode))
            ->first();

        if ($order === null) {
            return response()->json(['message' => 'Kode pelacakan pesanan tidak ditemukan.'], 404);
        }

        return response()->json([
            'data' => [
                'order_status' => $order->order_status->value,
                'order_status_label' => $order->order_status->label(),
                'payment_status' => $order->payment_status->value,
                'table_number' => $order->table_number,
                'total_price' => $order->total_price,
                'created_at' => $order->created_at?->toIso8601String(),
            ],
        ]);
    }
}
