<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * The endpoint is public, anyone may place an order.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'table_number' => ['nullable', 'string', 'max:10'],
            'phone' => ['sometimes', 'nullable', 'string', 'regex:/^[0-9+\-\s]{8,16}$/'],
            'name' => ['sometimes', 'nullable', 'string', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_id' => ['required', 'integer', 'exists:menus,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Normalised payload for OrderService::create().
     *
     * @return array{customer_id?: int|null, phone?: string|null, name?: string|null, table_number?: string|null, items: array<int, array{menu_id: int, quantity: int, notes?: string|null}>}
     */
    public function orderData(): array
    {
        return [
            'customer_id' => $this->integer('customer_id') ?: null,
            'phone' => $this->input('phone'),
            'name' => $this->input('name'),
            'table_number' => $this->input('table_number'),
            'items' => $this->input('items'),
        ];
    }
}
