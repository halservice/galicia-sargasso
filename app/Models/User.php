<?php

namespace App\Models;

use App\Enums\LLM;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\CanResetPassword;

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

    public function settings(): HasOne
    {
        return $this->HasOne(UserSetting::class);
    }
}
