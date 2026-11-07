<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Financial summary within a period.
     *
     * GET /admin/reports/summary?from=2026-11-01&to=2026-11-30
     * The default period is the current month.
     */
    public function summary(Request $request): JsonResponse
    {
        $range = $this->parseRange($request);

        $paidOrders = Order::query()
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$range['from'], $range['to']]);

        $summary = [
            'revenue' => (float) $paidOrders->sum('total_price'),
            'paid_orders' => (clone $paidOrders)->count(),
            'average_ticket' => (float) (clone $paidOrders)->avg('total_price'),
            'cancelled_orders' => Order::query()
                ->where('order_status', OrderStatus::Cancelled->value)
                ->whereBetween('created_at', [$range['from'], $range['to']])
                ->count(),
            'active_orders' => Order::query()
                ->whereNotIn('order_status', [OrderStatus::Completed->value, OrderStatus::Cancelled->value])
                ->count(),
            'unpaid_orders' => Order::query()
                ->where('payment_status', 'pending')
                ->whereNotIn('order_status', [OrderStatus::Completed->value, OrderStatus::Cancelled->value])
                ->count(),
        ];

        return response()->json([
            'data' => ['period' => [
                'from' => $range['from']->toDateString(),
                'to' => $range['to']->toDateString(),
            ]] + $summary,
        ]);
    }

    /**
     * Daily recap of paid orders (per calendar day).
     */
    public function daily(Request $request): JsonResponse
    {
        $range = $this->parseRange($request);

        $rows = Order::query()
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$range['from'], $range['to']])
            ->select([
                DB::raw('DATE(created_at) as day'),
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(total_price) as revenue'),
                DB::raw('AVG(total_price) as average_ticket'),
            ])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('day')
            ->get();

        return response()->json(['data' => $rows]);
    }

    /**
     * Menu items with the highest total sales on paid orders.
     */
    public function topItems(Request $request): JsonResponse
    {
        $limit = min($request->integer('limit', 10), 50);
        $range = $this->parseRange($request);

        $rows = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('menus', 'menus.id', '=', 'order_items.menu_id')
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$range['from'], $range['to']])
            ->select([
                'menus.id as menu_id',
                'menus.name as menu_name',
                DB::raw('SUM(order_items.quantity) as sold_quantity'),
                DB::raw('SUM(order_items.quantity * order_items.price) as revenue'),
            ])
            ->groupBy('menus.id', 'menus.name')
            ->orderByDesc('sold_quantity')
            ->orderBy('menu_name')
            ->limit($limit)
            ->get();

        return response()->json(['data' => $rows]);
    }

    /**
     * Resolve the reporting period from from/to query params
     * (month-to-date by default).
     *
     * @return array{from: Carbon, to: Carbon}
     */
    private function parseRange(Request $request): array
    {
        $validated = $request->validate([
            'from' => ['sometimes', 'nullable', 'date_format:Y-m-d'],
            'to' => ['sometimes', 'nullable', 'date_format:Y-m-d'],
        ]);

        $from = isset($validated['from'])
            ? Carbon::parse($validated['from'])->startOfDay()
            : Carbon::now()->startOfMonth();

        $to = isset($validated['to'])
            ? Carbon::parse($validated['to'])->endOfDay()
            : Carbon::now()->endOfDay();

        return ['from' => $from, 'to' => $to];
    }
}
