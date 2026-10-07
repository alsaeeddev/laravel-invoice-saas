<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;

class ExpensePolicy
{
    private function inSameBusiness(User $user, Expense $expense): bool
    {
        $businessId = session('current_business_id');
        return $businessId && $expense->business_id == $businessId
            && $user->businesses()->where('businesses.id', $businessId)->wherePivot('status', 'active')->exists();
    }

    public function viewAny(User $user): bool
    {
        return (bool) session('current_business_id');
    }

    public function view(User $user, Expense $expense): bool
    {
        return $this->inSameBusiness($user, $expense);
    }

    public function create(User $user): bool
    {
        return (bool) session('current_business_id');
    }

    public function update(User $user, Expense $expense): bool
    {
        return $this->inSameBusiness($user, $expense);
    }

    public function delete(User $user, Expense $expense): bool
    {
        return $this->inSameBusiness($user, $expense);
    }
}
