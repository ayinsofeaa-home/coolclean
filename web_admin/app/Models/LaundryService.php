<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaundryService extends Model
{
    protected $table = 'SERVICES';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];

    protected function casts(): array
    {
        return ['PRICE' => 'decimal:2'];
    }
}
