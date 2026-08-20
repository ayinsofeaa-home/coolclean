<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
class Booking extends Model
{
    protected $table = 'bookings';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'laundry_fee' => 'decimal:2',
            'service_fee' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'created_at' => 'datetime',
            'pickup_date_time' => 'datetime',
            'completed_at' => 'datetime',
            'rated_at' => 'datetime',
            'pickup_proof_at' => 'datetime',
            'delivery_proof_at' => 'datetime',
        ];
    }
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'id');
    }
    public function laundryLocation(): BelongsTo
    {
        return $this->belongsTo(LaundryLocation::class, 'laundry_location_id', 'id');
    }
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'booking_id', 'id');
    }
    public function earning(): HasOne
    {
        return $this->hasOne(BookingEarning::class, 'booking_id', 'id');
    }
    public function statusHistory(): HasMany
    {
        return $this->hasMany(BookingStatusHistory::class, 'booking_id', 'id');
    }
}