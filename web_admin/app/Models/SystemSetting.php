<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $table = 'SYSTEM_SETTINGS';

    protected $guarded = []; //tambah yang ni
    protected $primaryKey = 'SETTING_KEY';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
}