<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingEarning extends Model
{
    protected $table = 'BOOKING_EARNINGS';

    protected $guarded = []; //tambah yang ni
    protected $primaryKey = 'ID';
    public $timestamps = false;

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'BOOKING_ID', 'ID');
    }
}