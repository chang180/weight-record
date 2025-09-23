<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeightGoal extends Model
{
    use HasFactory;

    /**
     * 可批量賦值的屬性
     */
    protected $fillable = [
        'user_id',
        'target_weight',
        'target_date',
        'description',
        'is_active',
    ];

    /**
     * 屬性轉換
     */
    protected function casts(): array
    {
        return [
            'target_weight' => 'decimal:1',
            'target_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * 獲取擁有此目標的用戶
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 獲取活躍目標
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 計算目標進度百分比
     */
    public function getProgressPercentageAttribute(): float
    {
        $currentWeight = $this->user->weights()->latest('record_at')->first()?->weight;
        
        if (!$currentWeight) {
            return 0;
        }

        $startWeight = $this->user->weights()->oldest('record_at')->first()?->weight;
        
        if (!$startWeight) {
            return 0;
        }

        $totalChange = abs($this->target_weight - $startWeight);
        $currentChange = abs($currentWeight - $startWeight);
        
        if ($totalChange == 0) {
            return 100;
        }

        return min(100, ($currentChange / $totalChange) * 100);
    }
}
