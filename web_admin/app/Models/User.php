<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    // Adds Laravel notification features to this model.
    use Notifiable;

    // Use the existing table. Laravel will not create a new table.
    // Railway runs Linux/MySQL, where table names are case-sensitive.
    protected $table = 'USERS';

    // The existing table uses uppercase ID as its primary key.
    protected $primaryKey = 'ID';

    // Do not expect Laravel's created_at and updated_at columns.
    public $timestamps = false;

    protected $guarded = [];

    // Do not include these sensitive values in JSON responses.
    protected $hidden = [
        'PASSWORD_HASH',
        'FCM_TOKEN',
    ];

    protected function casts(): array
    {
        return [
            'CREATED_AT' => 'datetime',
            'FCM_TOKEN_UPDATED_AT' => 'datetime',
            'LOCKED_UNTIL' => 'datetime',
            'PASSWORD_CHANGE_REQUIRED' => 'boolean',
        ];
    }

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class, 'USER_ID', 'ID');
    }

    public function driver(): HasOne
    {
        return $this->hasOne(Driver::class, 'USER_ID', 'ID');
    }

    // Tell Laravel that the password is stored in PASSWORD_HASH.
    public function getAuthPasswordName(): string
    {
        return 'PASSWORD_HASH';
    }

    // Return the stored password hash when Laravel checks a password.
    public function getAuthPassword(): string
    {
        $hash = (string) $this->PASSWORD_HASH;

        // Java created the old hashes with $2a$.
        // PHP uses the compatible $2y$ name, so change only the prefix.
        // This does not update or change the value inside the database.
        if (str_starts_with($hash, '$2a$')) {
            return '$2y$'.substr($hash, 4);
        }

        return $hash;
    }
}
