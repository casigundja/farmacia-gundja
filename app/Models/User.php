<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'phone', 'password', 'role', 'status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => 'boolean',
        ];
    }

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class);
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isAttendant(): bool
    {
        return $this->role === 'attendant';
    }

    public function isStockist(): bool
    {
        return $this->role === 'stockist';
    }

    public function isEmployee(): bool
    {
        return in_array($this->role, ['employee', 'attendant', 'stockist']);
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer' || empty($this->role);
    }

    public function hasPermission(string $permission): bool
    {
        if (! $this->status) {
            return false;
        }

        if ($this->isAdmin()) {
            return true;
        }

        return $this->isEmployee() && ($this->employee?->hasPermission($permission) ?? false);
    }

    /** @param  array<int, string>  $permissions */
    public function hasAnyAdminPermission(array $permissions): bool
    {
        if (! $this->status) {
            return false;
        }

        if ($this->isAdmin()) {
            return true;
        }

        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }
}
