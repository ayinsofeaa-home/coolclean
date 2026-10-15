<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $table = 'SERVICES';

    protected $guarded = []; //tambah yang ni
    protected $primaryKey = 'ID';
    public $timestamps = false;
}