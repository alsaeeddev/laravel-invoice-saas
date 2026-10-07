<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function businesses()
    {
        return $this->belongsToMany(Business::class, 'business_user')
            ->withPivot('role', 'status', 'invited_at')
            ->withTimestamps();
    }

    public function currentBusiness(): ?Business
    {
        $businessId = session('current_business_id');
        if ($businessId) {
            return $this->businesses()->where('businesses.id', $businessId)->first();
        }
        return $this->businesses()->first();
    }

    public function isOwnerOf(Business $business): bool
    {
        return $this->businesses()
            ->where('businesses.id', $business->id)
            ->wherePivot('role', 'owner')
            ->exists();
    }

    public function roleInBusiness(Business $business): ?string
    {
        return $this->businesses()
            ->where('businesses.id', $business->id)
            ->first()?->pivot->role;
    }
}
