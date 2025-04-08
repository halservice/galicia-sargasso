<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property-read UserSetting|null $settings
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;
    protected $fillable = [
        'username',
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

    /**
     * @return HasOne<UserSetting>
     */
    public function settings(): HasOne
    {
        return $this->HasOne(UserSetting::class);
    }
}
