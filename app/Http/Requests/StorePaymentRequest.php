<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    /**
     * The endpoint is restricted by the role middleware.
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
            'method' => ['required', Rule::enum(PaymentMethod::class)],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999'],
        ];
    }

    /**
     * Typed payload for PaymentService::record().
     *
     * @return array{method: PaymentMethod, amount: float}
     */
    public function paymentData(): array
    {
        return [
            'method' => PaymentMethod::from($this->string('method')),
            'amount' => (float) $this->input('amount'),
        ];
    }
}
