<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    protected $fillable = [
        'email',
        'password'
    ];

    protected $hidden = [
        'password'
    ];

    protected static function booted(): void
    {
        static::created(function ($user) {
            UserSetting::create([
                'user_id' => $user->id,
            ]);
        });
    }

}
