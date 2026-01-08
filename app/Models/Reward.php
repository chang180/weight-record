<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reward extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reward_type',
        'reward_name',
        'points_spent',
        'redeemed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'points_spent' => 'integer',
            'redeemed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
