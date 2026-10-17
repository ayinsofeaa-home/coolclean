<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaundryLocation extends Model
{
    protected $table = 'LAUNDRY_LOCATIONS';

    protected $guarded = []; //tambah yang ni
    protected $primaryKey = 'ID';
    public $timestamps = false;
}