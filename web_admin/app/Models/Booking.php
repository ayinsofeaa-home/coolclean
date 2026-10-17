<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    protected $table = 'BOOKINGS';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'TOTAL_AMOUNT' => 'decimal:2',
            'LAUNDRY_FEE' => 'decimal:2',
            'SERVICE_FEE' => 'decimal:2',
            'DELIVERY_FEE' => 'decimal:2',
            'CREATED_AT' => 'datetime',
            'PICKUP_DATE_TIME' => 'datetime',
            'COMPLETED_AT' => 'datetime',
            'RATED_AT' => 'datetime',
            'PICKUP_PROOF_AT' => 'datetime',
            'DELIVERY_PROOF_AT' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'CUSTOMER_ID', 'ID');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'DRIVER_ID', 'ID');
    }

    public function laundryLocation(): BelongsTo
    {
        return $this->belongsTo(LaundryLocation::class, 'LAUNDRY_LOCATION_ID', 'ID');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'BOOKING_ID', 'ID');
    }

    public function earning(): HasOne
    {
        return $this->hasOne(BookingEarning::class, 'BOOKING_ID', 'ID');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(BookingStatusHistory::class, 'BOOKING_ID', 'ID');
    }
}
