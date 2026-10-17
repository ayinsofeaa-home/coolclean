<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerWalletTransaction extends Model
{
    protected $table = 'CUSTOMER_WALLET_TRANSACTIONS';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'AMOUNT' => 'decimal:2',
            'CREATED_AT' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'CUSTOMER_ID', 'ID');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'BOOKING_ID', 'ID');
    }
}
