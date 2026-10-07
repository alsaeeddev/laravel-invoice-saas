<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    use BelongsToTenant;

    protected $fillable = ['business_id', 'name'];

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'category_id');
    }
}
