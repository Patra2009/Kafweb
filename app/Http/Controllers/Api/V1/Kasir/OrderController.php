<?php

namespace App\Http\Controllers\Api\V1\Kasir;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Http\Resources\PaymentResource;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly PaymentService $paymentService,
    ) {}

    /**
     * Incoming orders for the cashier dashboard, the latest first.
     *
     * Example: GET /kasir/orders?order_status=processing&date=2026-11-02
     * Without order_status the endpoint returns active (unfinished) orders.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'order_status' => ['sometimes', 'nullable', Rule::enum(OrderStatus::class)],
            'payment_status' => ['sometimes', 'nullable', Rule::enum(PaymentStatus::class)],
            'date' => ['sometimes', 'nullable', 'date'],
        ]);

        $orders = Order::query()
            ->with([
                'items.menu:id,name,price',
                'customer:id,name,phone',
                'payments',
            ])
            ->when(
                $filters['order_status'] ?? null,
                fn ($query, string $status) => $query->where('order_status', $status),
                fn ($query) => $query->whereNotIn('order_status', [OrderStatus::Completed->value, OrderStatus::Cancelled->value]),
            )
            ->when(
                $filters['payment_status'] ?? null,
                fn ($query, string $status) => $query->where('payment_status', $status),
            )
            ->when(
                $filters['date'] ?? null,
                fn ($query, string $date) => $query->whereDate('created_at', $date),
            )
            ->latest()
            ->get();

        return OrderResource::collection($orders);
    }

    /**
     * Detail of one order for the cashier.
     */
    public function show(Order $order): OrderResource
    {
        return new OrderResource($order->loadMissing([
            'items.menu:id,name,price',
            'customer:id,name,phone',
            'payments.cashier:id,name',
        ]));
    }

    /**
     * Walk-in order created directly by the cashier at the counter
     * (same business rules as the customer checkout).
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->create($request->orderData());

        return (new OrderResource($order))->response()->setStatusCode(201);
    }

    /**
     * Move an order through the pipeline:
     * pending -> processing -> ready -> completed.
     */
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): OrderResource
    {
        $status = OrderStatus::from($request->validated('order_status'));

        return new OrderResource($this->orderService->changeStatus($order, $status));
    }

    /**
     * Record the payment; the order is settled atomically.
     */
    public function pay(StorePaymentRequest $request, Order $order): JsonResponse
    {
        $paymentData = $request->paymentData();

        $result = $this->paymentService->record(
            cashier: $request->user(),
            order: $order,
            method: $paymentData['method'],
            amount: $paymentData['amount'],
        );

        return response()->json([
            'data' => new PaymentResource($result['payment']->loadMissing('cashier:id,name')),
            'change' => $result['change'],
            'message' => 'Pembayaran tercatat.',
        ]);
    }
}
