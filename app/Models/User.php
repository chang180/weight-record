<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'height',
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
        'height' => 'decimal:2',
    ];

    /**
     * 獲取用戶的所有體重記錄
     */
    public function weights()
    {
        return $this->hasMany(Weight::class);
    }

    /**
     * 獲取用戶的所有體重目標
     */
    public function weightGoals()
    {
        return $this->hasMany(WeightGoal::class);
    }

    /**
     * 獲取用戶的活躍體重目標
     */
    public function activeWeightGoal()
    {
        return $this->hasOne(WeightGoal::class)->where('is_active', true);
    }
}
