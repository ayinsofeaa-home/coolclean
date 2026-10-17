<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QueuedEmail extends Model
{
    protected $table = 'EMAIL_QUEUE';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'CREATED_AT' => 'datetime',
            'LAST_ATTEMPT_AT' => 'datetime',
            'SENT_AT' => 'datetime',
        ];
    }
}
