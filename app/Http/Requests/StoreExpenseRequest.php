<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'          => ['required', 'string', 'max:255'],
            'category_id'    => ['nullable', 'exists:expense_categories,id'],
            'amount'         => ['required', 'numeric', 'min:0.01'],
            'expense_date'   => ['required', 'date'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'notes'          => ['nullable', 'string', 'max:2000'],
            'receipt'        => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }
}
