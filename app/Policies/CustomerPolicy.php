<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{
    private function inSameBusiness(User $user, Customer $customer): bool
    {
        $businessId = session('current_business_id');
        return $businessId && $customer->business_id == $businessId
            && $user->businesses()->where('businesses.id', $businessId)->wherePivot('status', 'active')->exists();
    }

    public function viewAny(User $user): bool
    {
        return (bool) session('current_business_id');
    }

    public function view(User $user, Customer $customer): bool
    {
        return $this->inSameBusiness($user, $customer);
    }

    public function create(User $user): bool
    {
        return (bool) session('current_business_id');
    }

    public function update(User $user, Customer $customer): bool
    {
        return $this->inSameBusiness($user, $customer);
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $this->inSameBusiness($user, $customer);
    }
}
