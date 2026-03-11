<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Salon extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'name', 'legal_name', 'slug', 'currency', 'currency_code', 'timezone', 'status',
        'email', 'phone', 'city', 'country_code', 'booking_enabled', 'loyalty_enabled', 'inventory_enabled', 'is_active'
    ];

    public function getCurrencyAttribute($value)
    {
        return $value ?? $this->currency_code;
    }

    protected $casts = [
        'booking_enabled' => 'boolean',
        'loyalty_enabled' => 'boolean',
        'inventory_enabled' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function members(): HasMany
    {
        return $this->hasMany(SalonMember::class);
    }
}
