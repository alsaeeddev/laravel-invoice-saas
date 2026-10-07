<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    private function inSameBusiness(User $user, Invoice $invoice): bool
    {
        $businessId = session('current_business_id');
        return $businessId && $invoice->business_id == $businessId
            && $user->businesses()->where('businesses.id', $businessId)->wherePivot('status', 'active')->exists();
    }

    public function viewAny(User $user): bool
    {
        return (bool) session('current_business_id');
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $this->inSameBusiness($user, $invoice);
    }

    public function create(User $user): bool
    {
        return (bool) session('current_business_id');
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $this->inSameBusiness($user, $invoice) && $invoice->status !== 'cancelled';
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $this->inSameBusiness($user, $invoice) && $invoice->status === 'draft';
    }

    public function cancel(User $user, Invoice $invoice): bool
    {
        return $this->inSameBusiness($user, $invoice);
    }

    public function send(User $user, Invoice $invoice): bool
    {
        return $this->inSameBusiness($user, $invoice) && $invoice->status === 'draft';
    }
}
