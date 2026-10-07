<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'business_id', 'name', 'company', 'email',
        'phone', 'address', 'city', 'country', 'notes',
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function totalInvoiced(): float
    {
        return (float) $this->invoices()->sum('total');
    }

    public function totalPaid(): float
    {
        return (float) $this->invoices()->sum('amount_paid');
    }

    public function totalOutstanding(): float
    {
        return $this->totalInvoiced() - $this->totalPaid();
    }
}
