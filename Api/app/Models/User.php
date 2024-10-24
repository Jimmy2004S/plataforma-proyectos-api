<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_name',
        'email',
        'password',
        'estado',
        'code',
        'description',
        'role_id',
        'state'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function student(): HasOne
    {
        return $this->hasOne(InforStudent::class, 'user_code', 'code');
    }

    public function teacher(): HasOne
    {
        return $this->hasOne(InforTeacher::class, 'user_code', 'code');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
