<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'CUSTOMERS';

    protected $guarded = [];
    protected $primaryKey = 'ID';
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class, 'USER_ID', 'ID');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'CUSTOMER_ID', 'ID');
    }

    public function walletTransactions()
    {
        return $this->hasMany(CustomerWalletTransaction::class, 'CUSTOMER_ID', 'ID');
    }
}