<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'icon',
        'type',
        'requirement_value',
        'points_reward',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'requirement_value' => 'decimal:1',
            'points_reward' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_achievements')
            ->withPivot('unlocked_at', 'weight_at_unlock')
            ->withTimestamps();
    }

    /**
     * 檢查用戶是否已解鎖此成就
     */
    public function isUnlockedBy(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }
}
