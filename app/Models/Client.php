<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    protected $connection = 'tenant';

    use SoftDeletes;

    protected $fillable = [
        'salon_id', 'first_name', 'last_name', 'phone', 'email', 'gender', 'date_of_birth', 'status',
        'notes', 'marketing_consent', 'sms_consent', 'whatsapp_consent', 'email_consent',
        'loyalty_points_balance', 'total_spent', 'total_visits', 'last_visit_at', 'is_active'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'marketing_consent' => 'boolean',
        'sms_consent' => 'boolean',
        'whatsapp_consent' => 'boolean',
        'email_consent' => 'boolean',
        'loyalty_points_balance' => 'integer',
        'total_spent' => 'decimal:2',
        'total_visits' => 'integer',
        'last_visit_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }

    public function consents(): HasMany
    {
        return $this->hasMany(ClientConsent::class);
    }

    public function medicalProfile()
    {
        return $this->hasOne(MedicalProfile::class);
    }

    public function treatments(): HasMany
    {
        return $this->hasMany(ClientTreatment::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ClientAttachment::class);
    }
}
