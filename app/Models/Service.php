<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Service extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'salon_id', 'category_id', 'name', 'description', 'duration_min', 'duration_minutes', 'buffer_min',
        'price_cents', 'price', 'currency', 'is_active', 'sort_order', 'image_url',
        'online_booking_enabled', 'requires_deposit', 'deposit_amount'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'online_booking_enabled' => 'boolean',
        'requires_deposit' => 'boolean',
        'deposit_amount' => 'decimal:2',
        'price' => 'decimal:2',
    ];

    public function getDurationMinAttribute($value)
    {
        return $value ?? $this->duration_minutes;
    }

    public function getPriceCentsAttribute($value)
    {
        if ($value)
            return $value;
        return $this->price ? (int)($this->price * 100) : 0;
    }

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class , 'category_id');
    }

    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(Staff::class , 'service_staff')
            ->withPivot(['salon_id', 'price_cents_override', 'duration_min_override', 'is_active'])
            ->withTimestamps();
    }
}
