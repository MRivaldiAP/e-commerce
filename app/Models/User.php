<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Concerns\UsesApplicationConnection;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use UsesApplicationConnection;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    public const ROLE_ADMINISTRATOR = 'administrator';
    public const ROLE_PRODUCT_MANAGER = 'product_manager';
    public const ROLE_ORDER_MANAGER = 'order_manager';
    public const ROLE_BASIC = 'basic';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'role' => 'string',
    ];

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function hasAnyRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isAdministrator(): bool
    {
        return $this->hasRole(self::ROLE_ADMINISTRATOR);
    }

    public function isProductManager(): bool
    {
        return $this->hasRole(self::ROLE_PRODUCT_MANAGER);
    }

    public function isOrderManager(): bool
    {
        return $this->hasRole(self::ROLE_ORDER_MANAGER);
    }

    public function isBasic(): bool
    {
        return $this->hasRole(self::ROLE_BASIC);
    }

    public function addresses(): HasMany {
        return $this->hasMany(Address::class);
    }

    public function orders(): HasMany {
        return $this->hasMany(Order::class);
    }

    public function cart(): HasOne {
        return $this->hasOne(Cart::class);
    }
}
