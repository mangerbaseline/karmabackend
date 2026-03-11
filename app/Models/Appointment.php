<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'salon_id',
        'client_id',
        'service_id',
        'staff_id',
        'staff_profile_id',
        'user_id',
        'booking_channel',
        'appointment_date',
        'start_at',
        'end_at',
        'status',
        'subtotal',
        'discount_amount',
        'total_amount',
        'deposit_amount',
        'notes',
        'customer_name',
        'customer_phone',
        'customer_email',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
    ];

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
    // For compatibility with SQL 'staff_profiles'
    public function getStaffProfileIdAttribute()
    {
        return $this->staff_id;
    }

    public function setStaffProfileIdAttribute($value)
    {
        $this->staff_id = $value;
    }
}
