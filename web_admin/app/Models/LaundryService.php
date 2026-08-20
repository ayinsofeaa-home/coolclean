<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LaundryService extends Model
{
    protected $table = 'services';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
    protected function casts(): array
    {
        return ['price' => 'decimal:2'];
    }
}