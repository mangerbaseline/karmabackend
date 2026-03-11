<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Staff extends Model
{
    protected $connection = 'tenant';

    protected $table = 'staff_profiles';

    protected $fillable = [
        'salon_id',
        'user_id',
        'name',
        'title',
        'job_title',
        'is_active',
        'sort_order',
        'avatar_url',
        'commission_percent',
        'employment_type',
        'can_take_bookings',
        'is_visible_online',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Alias for SQL compatibility
    public function getJobTitleAttribute($value)
    {
        return $value ?? $this->title;
    }

    public function setJobTitleAttribute($value)
    {
        $this->title = $value;
        $this->attributes['job_title'] = $value;
    }

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class , 'service_staff')
            ->withPivot(['salon_id', 'price_cents_override', 'duration_min_override', 'is_active'])
            ->withTimestamps();
    }
}
