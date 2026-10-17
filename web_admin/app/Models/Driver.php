<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $table = 'DRIVERS';

    protected $guarded = []; //tambah yang ni
    protected $primaryKey = 'ID';
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class, 'USER_ID', 'ID');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'DRIVER_ID', 'ID');
    }
}