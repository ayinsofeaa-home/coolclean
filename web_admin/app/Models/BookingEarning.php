<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class BookingEarning extends Model
{
    protected $table = 'booking_earnings';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
    protected function casts(): array
    {
        return [
            'booking_amount' => 'decimal:2',
            'driver_percentage' => 'decimal:2',
            'admin_percentage' => 'decimal:2',
            'driver_earning' => 'decimal:2',
            'admin_earning' => 'decimal:2',
            'earned_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'id');
    }
}