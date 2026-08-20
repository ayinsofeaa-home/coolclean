<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class QueuedEmail extends Model
{
    protected $table = 'email_queue';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'last_attempt_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }
}