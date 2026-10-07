<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $invoice = $this->route('invoice');
        $maxRefundable = $invoice ? $invoice->amount_paid : 0;

        return [
            'amount'           => ['required', 'numeric', 'min:0.01', 'max:' . $maxRefundable],
            'payment_date'     => ['required', 'date'],
            'method'           => ['required', 'in:cash,bank_transfer,card,other'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes'            => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.max' => 'Refund amount cannot exceed the amount already paid (:max).',
        ];
    }
}