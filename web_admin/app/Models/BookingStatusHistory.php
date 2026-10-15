<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingStatusHistory extends Model
{
    protected $table = 'BOOKING_STATUS_HISTORY';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];

    protected function casts(): array
    {
        return ['UPDATED_AT' => 'datetime'];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'BOOKING_ID', 'ID');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'UPDATED_BY_USER_ID', 'ID');
    }
}
