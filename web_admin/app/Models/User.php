<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
class User extends Authenticatable
{
    // Adds Laravel notification features to this model.
    use Notifiable;
    // Use the existing table.
    protected $table = 'users';
    // The table uses lowercase id as its primary key.
    protected $primaryKey = 'id';
    // Do not expect Laravel's created_at and updated_at columns.
    public $timestamps = false;
    protected $guarded = [];
    // Do not include these sensitive values in JSON responses.
    protected $hidden = [
        'password_hash',
        'fcm_token',
    ];
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'fcm_token_updated_at' => 'datetime',
            'locked_until' => 'datetime',
            'password_change_required' => 'boolean',
        ];
    }
    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class, 'user_id', 'id');
    }
    public function driver(): HasOne
    {
        return $this->hasOne(Driver::class, 'user_id', 'id');
    }
    // Tell Laravel that the password is stored in password_hash.
    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }
    // Return the stored password hash when Laravel checks a password.
    public function getAuthPassword(): string
    {
        $hash = (string) $this->password_hash;
        // Java created the old hashes with $2a$.
        // PHP uses the compatible $2y$ name, so change only the prefix.
        // This does not update or change the value inside the database.
        if (str_starts_with($hash, '$2a$')) {
            return '$2y$'.substr($hash, 4);
        }
        return $hash;
    }
}