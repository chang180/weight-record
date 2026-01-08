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
        'start_weight',
        'total_points',
        'available_points',
        'current_streak',
        'longest_streak',
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
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'height' => 'decimal:2',
            'start_weight' => 'decimal:1',
            'total_points' => 'integer',
            'available_points' => 'integer',
            'current_streak' => 'integer',
            'longest_streak' => 'integer',
        ];
    }

    /**
     * 獲取用戶的所有體重記錄
     */
    public function weights(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Weight::class);
    }

    /**
     * 獲取用戶的所有體重目標
     */
    public function weightGoals(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WeightGoal::class);
    }

    /**
     * 獲取用戶的活躍體重目標
     */
    public function activeWeightGoal(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(WeightGoal::class)->where('is_active', true);
    }

    /**
     * 每日記錄
     */
    public function dailyLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DailyLog::class);
    }

    /**
     * 成就（多對多）
     */
    public function achievements(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Achievement::class, 'user_achievements')
            ->withPivot('unlocked_at', 'weight_at_unlock')
            ->withTimestamps()
            ->orderByPivot('unlocked_at', 'desc');
    }

    /**
     * 獎勵兌換記錄
     */
    public function rewards(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Reward::class);
    }

    /**
     * 取得當前體重
     */
    public function getCurrentWeightAttribute(): ?float
    {
        return $this->weights()->latest('record_at')->first()?->weight;
    }

    /**
     * 計算 BMI
     */
    public function getBmiAttribute(): ?float
    {
        if (!$this->height || !$this->current_weight) {
            return null;
        }
        $heightInMeters = $this->height / 100;
        return round($this->current_weight / ($heightInMeters * $heightInMeters), 1);
    }

    /**
     * 計算減重進度百分比
     */
    public function getProgressPercentageAttribute(): float
    {
        if (!$this->start_weight || !$this->current_weight) {
            return 0;
        }
        $totalChange = abs($this->start_weight - 80); // 目標是 80kg
        $currentChange = abs($this->start_weight - $this->current_weight);
        if ($totalChange == 0) {
            return 100;
        }
        return min(100, ($currentChange / $totalChange) * 100);
    }

    /**
     * 計算潛在節省金額（每減 1kg = NT$6,000）
     */
    public function getPotentialSavingsAttribute(): int
    {
        if (!$this->start_weight || !$this->current_weight) {
            return 0;
        }
        $weightLost = $this->start_weight - $this->current_weight;
        return (int)($weightLost * 6000);
    }

    /**
     * 生成個人化的體重里程碑列表
     * 根據起始體重和目標體重，平均分配 7 個里程碑
     */
    public function getWeightMilestonesAttribute(): array
    {
        $cacheKey = "user.{$this->id}.weight_milestones";

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 1800, function () {
            $startWeight = $this->start_weight;
            $targetWeight = $this->activeWeightGoal?->target_weight ?? 80.0;

            if (!$startWeight || $startWeight <= $targetWeight) {
                return [];
            }

            $totalDifference = $startWeight - $targetWeight;
            $step = $totalDifference / 7; // 平均分成 7 個階段

            $milestones = [];
            $icons = ['🎖️', '🎖️', '🏅', '🏅', '🏆', '🏆', '👑'];
            $names = ['起步者', '認真了', '初見成效', '過半', '大有進展', '接近目標', '終極勝利'];

            for ($i = 1; $i <= 7; $i++) {
                $weight = round($startWeight - ($step * $i), 1);
                $progress = ($i / 7) * 100;

                $description = $this->getGeneratedMilestoneDescription($i, $weight, $progress);

                $milestones[] = [
                    'stage' => $i,
                    'name' => $names[$i - 1],
                    'icon' => $icons[$i - 1],
                    'weight' => $weight,
                    'progress' => round($progress),
                    'description' => $description,
                ];
            }

            return $milestones;
        });
    }

    /**
     * 清除里程碑快取
     */
    public function clearWeightMilestonesCache(): void
    {
        \Illuminate\Support\Facades\Cache::forget("user.{$this->id}.weight_milestones");
    }

    /**
     * 生成里程碑描述
     */
    private function getGeneratedMilestoneDescription(int $stage, float $weight, float $progress): string
    {
        $descriptions = [
            1 => "萬事起頭難，你已經邁出第一步！目標：{$weight}kg",
            2 => "連續達成目標，證明你是認真的！目標：{$weight}kg",
            3 => "初見成效！已完成 " . round($progress) . "% 的旅程！",
            4 => "太棒了！已經完成一半的旅程！",
            5 => "大有進展！勝利在望！",
            6 => "接近目標了！再堅持一下！",
            7 => "恭喜！你靠自己的意志力達成目標！",
        ];

        return $descriptions[$stage] ?? '';
    }
}
