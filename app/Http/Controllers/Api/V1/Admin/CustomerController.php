<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomerController extends Controller
{
    /**
     * List of registered customers.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $search = $request->string('search');

        $customers = Customer::query()
            ->withCount('orders')
            ->when($search->isNotEmpty(), fn ($query) => $query
                ->where('name', 'like', '%'.$search.'%')
                ->orWhere('phone', 'like', '%'.$search.'%'))
            ->orderBy('name')
            ->paginate(min($request->integer('per_page', 15), 50));

        return CustomerResource::collection($customers);
    }

    /**
     * Add a customer record.
     */
    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = Customer::create($request->validated());

        return (new CustomerResource($customer))->response()->setStatusCode(201);
    }

    /**
     * Edit customer data.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer): CustomerResource
    {
        $customer->update($request->validated());

        return new CustomerResource($customer);
    }

    /**
     * Deleting customers with an order history is refused because the
     * financial report will lose the customer's identity reference.
     */
    public function destroy(Customer $customer): JsonResponse
    {
        if ($customer->orders()->exists()) {
            abort(409, 'Pelanggan masih memiliki riwayat pesanan sehingga tidak dapat dihapus.');
        }

        $customer->delete();

        return response()->json(['message' => 'Pelanggan dihapus.']);
    }
}
