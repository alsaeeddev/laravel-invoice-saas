<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Business extends Model
{
    protected $fillable = [
        'name', 'logo_path', 'email', 'phone', 'address',
        'city', 'country', 'website', 'tax_number', 'currency',
        'invoice_prefix', 'next_invoice_number', 'default_tax_rate',
        'invoice_notes', 'invoice_terms',
    ];

    /**
     * Atomically reserve and return the next invoice number string.
     * Uses a database lock to prevent collisions under concurrent requests.
     */
    public function reserveNextInvoiceNumber(): string
    {
        return DB::transaction(function () {
            $business = Business::lockForUpdate()->findOrFail($this->id);
            $number = $business->next_invoice_number;
            $business->increment('next_invoice_number');

            return $business->invoice_prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
        });
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'business_user')
            ->withPivot('role', 'status', 'invited_at')
            ->withTimestamps();
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function expenseCategories()
    {
        return $this->hasMany(ExpenseCategory::class);
    }
}
