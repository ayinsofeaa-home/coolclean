<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'CUSTOMERS';
    
     protected $guarded = []; //tambah yang ni
    protected $primaryKey = 'ID';
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class, 'USER_ID', 'ID');
    }
}