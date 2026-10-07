<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    private function inSameBusiness(User $user, Product $product): bool
    {
        $businessId = session('current_business_id');
        return $businessId && $product->business_id == $businessId
            && $user->businesses()->where('businesses.id', $businessId)->wherePivot('status', 'active')->exists();
    }

    public function viewAny(User $user): bool
    {
        return (bool) session('current_business_id');
    }

    public function view(User $user, Product $product): bool
    {
        return $this->inSameBusiness($user, $product);
    }

    public function create(User $user): bool
    {
        return (bool) session('current_business_id');
    }

    public function update(User $user, Product $product): bool
    {
        return $this->inSameBusiness($user, $product);
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->inSameBusiness($user, $product);
    }
}
