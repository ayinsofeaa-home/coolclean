<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class LaundryLocation extends Model
{
    protected $table = 'laundry_locations';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'laundry_location_id', 'id');
    }
}
