<?php

namespace App\Http\Controllers\Api\V1\Kasir;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomerController extends Controller
{
    /**
     * Customer lookup screen for the cashier (with a quick search
     * either by name or phone number).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $search = $request->string('search');

        $customers = Customer::query()
            ->when($search->isNotEmpty(), fn ($query) => $query
                ->where('name', 'like', '%'.$search.'%')
                ->orWhere('phone', 'like', '%'.$search.'%'))
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(min($request->integer('per_page', 10), 50));

        return CustomerResource::collection($customers);
    }

    /**
     * Register a customer; if the phone number is already registered
     * the existing record is returned (tambah/pilih in one endpoint).
     */
    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = Customer::firstOrCreate(
            attributes: ['phone' => $request->string('phone')->trim()],
            values: ['name' => $request->string('name')->trim()],
        );

        return response()->json([
            'data' => new CustomerResource($customer),
            'message' => $customer->wasRecentlyCreated
                ? 'Pelanggan baru tersimpan.'
                : 'Nomor telepon sudah terdaftar, menggunakan data pelanggan yang ada.',
        ], $customer->wasRecentlyCreated ? 201 : 200);
    }
}
