# 減重遊戲化功能整合計劃

## 📋 專案概述

將減重遊戲化系統（成就、任務、積分、獎勵）整合到現有的體重記錄器專案中，透過遊戲化機制提升用戶持續減重的動力。

**參考文件**：`.ai-dev/issue/減重遊戲功能/sparkle.md`

---

## ✅ 可行性分析

### 技術可行性：**高度可行**

1. **技術棧完全匹配**
   - 現有 Laravel 12 + PHP 8.4 架構可完美支援
   - Blade + Tailwind CSS 可實現遊戲化 UI
   - Chart.js 已存在，可用於統計視覺化
   - Alpine.js 可處理互動功能

2. **現有基礎優勢**
   - ✅ 已有完整的體重記錄系統（`Weight` 模型）
   - ✅ 已有體重目標功能（`WeightGoal` 模型）
   - ✅ User 模型已有 `height` 欄位（可用於 BMI 計算）
   - ✅ 已有統計圖表功能
   - ✅ 已有用戶認證系統（Laravel Breeze）

3. **擴展性良好**
   - Laravel 的 Eloquent ORM 易於建立新關聯
   - 現有路由結構可擴展
   - 資料庫遷移機制完善

### 實作複雜度：**中等偏高**

需要新增：
- 4 個新資料表（daily_logs, achievements, user_achievements, rewards）
- 3-4 個新控制器
- 10+ 個新視圖
- 多個服務類別（積分計算、成就檢查、任務驗證）
- 遊戲化 UI 元件

### 預估工作量

- **Phase 1（核心功能）**：2-3 週
- **Phase 2（遊戲化元素）**：1-2 週
- **Phase 3（進階功能）**：1-2 週
- **總計**：4-7 週（依開發速度而定）

---

## 🗄️ 資料庫設計

### 1. 擴展 `users` 表

**遷移檔案**：`database/migrations/YYYY_MM_DD_HHMMSS_add_gamification_fields_to_users_table.php`

```php
Schema::table('users', function (Blueprint $table) {
    $table->decimal('start_weight', 4, 1)->nullable()->comment('起始體重(kg)');
    $table->integer('total_points')->default(0)->comment('總積分');
    $table->integer('available_points')->default(0)->comment('可用積分');
    $table->integer('current_streak')->default(0)->comment('當前連續達成天數');
    $table->integer('longest_streak')->default(0)->comment('最長連續達成天數');
});
```

**說明**：
- `start_weight`：用於計算減重進度，可在首次記錄體重時自動設定，或允許用戶手動設定
- `total_points`：累積的所有積分（包含已兌換的）
- `available_points`：目前可用的積分（未兌換的）
- `current_streak`：當前連續完成所有每日任務的天數
- `longest_streak`：歷史最長連續達成天數

### 2. 新增 `daily_logs` 表

**遷移檔案**：`database/migrations/YYYY_MM_DD_HHMMSS_create_daily_logs_table.php`

```php
Schema::create('daily_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->date('date');
    $table->decimal('weight', 4, 1)->nullable()->comment('當日體重');
    
    // 每日任務欄位
    $table->boolean('task_meal')->default(false)->comment('用餐任務完成');
    $table->boolean('task_walk')->default(false)->comment('散步任務完成');
    $table->boolean('task_no_snack')->default(false)->comment('不吃宵夜完成');
    $table->boolean('task_sleep')->default(false)->comment('早睡任務完成');
    $table->boolean('task_no_sugar')->default(false)->comment('不喝糖飲完成(假日)');
    
    // 積分欄位
    $table->integer('daily_points')->default(0)->comment('當日任務積分');
    $table->integer('weekly_points')->default(0)->comment('週任務積分');
    
    // 其他
    $table->text('notes')->nullable()->comment('備註');
    $table->timestamps();
    
    // 索引
    $table->unique(['user_id', 'date']);
    $table->index(['user_id', 'date']);
});
```

**說明**：
- 每個用戶每天只能有一筆記錄（使用 unique 約束）
- `weight` 可為 null，因為可能只記錄任務而不記錄體重
- 任務欄位使用 boolean，true 表示完成
- `daily_points` 和 `weekly_points` 分別記錄每日和週任務積分

### 3. 新增 `achievements` 表（成就定義）

**遷移檔案**：`database/migrations/YYYY_MM_DD_HHMMSS_create_achievements_table.php`

```php
Schema::create('achievements', function (Blueprint $table) {
    $table->id();
    $table->string('code')->unique()->comment('成就代碼');
    $table->string('name')->comment('成就名稱');
    $table->text('description')->comment('成就描述');
    $table->string('icon')->comment('成就圖示(emoji)');
    $table->enum('type', ['weight_milestone', 'special', 'streak'])->comment('成就類型');
    $table->decimal('requirement_value', 4, 1)->nullable()->comment('需求值(如體重值)');
    $table->integer('points_reward')->default(0)->comment('獎勵積分');
    $table->integer('sort_order')->default(0)->comment('排序順序');
    $table->timestamps();
    
    $table->index('type');
    $table->index('code');
});
```

**說明**：
- `code`：唯一識別碼，用於程式邏輯判斷（如 'weight_107', 'perfect_week'）
- `type`：區分成就類型，方便分類顯示和檢查
- `requirement_value`：用於體重里程碑成就，記錄目標體重值
- `sort_order`：控制成就顯示順序

### 4. 新增 `user_achievements` 表（用戶成就記錄）

**遷移檔案**：`database/migrations/YYYY_MM_DD_HHMMSS_create_user_achievements_table.php`

```php
Schema::create('user_achievements', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('achievement_id')->constrained()->onDelete('cascade');
    $table->timestamp('unlocked_at')->comment('解鎖時間');
    $table->decimal('weight_at_unlock', 4, 1)->nullable()->comment('解鎖時體重');
    $table->timestamps();
    
    $table->unique(['user_id', 'achievement_id']);
    $table->index(['user_id', 'unlocked_at']);
});
```

**說明**：
- 使用 unique 約束確保每個用戶每個成就只能解鎖一次
- `unlocked_at` 記錄解鎖時間，用於排序和統計
- `weight_at_unlock` 記錄解鎖時的體重，用於體重里程碑成就

### 5. 新增 `rewards` 表（獎勵兌換記錄）

**遷移檔案**：`database/migrations/YYYY_MM_DD_HHMMSS_create_rewards_table.php`

```php
Schema::create('rewards', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('reward_type')->comment('獎勵類型');
    $table->string('reward_name')->comment('獎勵名稱');
    $table->integer('points_spent')->comment('花費積分');
    $table->timestamp('redeemed_at')->comment('兌換時間');
    $table->text('notes')->nullable()->comment('備註');
    $table->timestamps();
    
    $table->index(['user_id', 'redeemed_at']);
});
```

**說明**：
- `reward_type`：獎勵類型（如 'indulgence_meal', 'small_reward', 'family_time' 等）
- `reward_name`：獎勵名稱（如 '放縱餐券', '小確幸' 等）
- `points_spent`：記錄花費的積分，用於統計

---

## 📦 模型設計

### 1. 擴展 `User` 模型

**檔案**：`app/Models/User.php`

**新增欄位到 `$fillable`**：
```php
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
```

**新增 casts**：
```php
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
```

**新增關聯方法**：
```php
// 每日記錄
public function dailyLogs(): HasMany
{
    return $this->hasMany(DailyLog::class);
}

// 成就（多對多）
public function achievements(): BelongsToMany
{
    return $this->belongsToMany(Achievement::class, 'user_achievements')
        ->withPivot('unlocked_at', 'weight_at_unlock')
        ->withTimestamps()
        ->orderByPivot('unlocked_at', 'desc');
}

// 獎勵兌換記錄
public function rewards(): HasMany
{
    return $this->hasMany(Reward::class);
}
```

**新增存取器方法**：
```php
// 取得當前體重
public function getCurrentWeightAttribute(): ?float
{
    return $this->weights()->latest('record_at')->first()?->weight;
}

// 計算 BMI
public function getBmiAttribute(): ?float
{
    if (!$this->height || !$this->current_weight) {
        return null;
    }
    $heightInMeters = $this->height / 100;
    return round($this->current_weight / ($heightInMeters * $heightInMeters), 1);
}

// 計算減重進度百分比
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

// 計算潛在節省金額（每減 1kg = NT$6,000）
public function getPotentialSavingsAttribute(): int
{
    if (!$this->start_weight || !$this->current_weight) {
        return 0;
    }
    $weightLost = $this->start_weight - $this->current_weight;
    return (int)($weightLost * 6000);
}
```

### 2. 建立 `DailyLog` 模型

**檔案**：`app/Models/DailyLog.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'weight',
        'task_meal',
        'task_walk',
        'task_no_snack',
        'task_sleep',
        'task_no_sugar',
        'daily_points',
        'weekly_points',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'weight' => 'decimal:1',
            'task_meal' => 'boolean',
            'task_walk' => 'boolean',
            'task_no_snack' => 'boolean',
            'task_sleep' => 'boolean',
            'task_no_sugar' => 'boolean',
            'daily_points' => 'integer',
            'weekly_points' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // 檢查是否完成所有任務
    public function isAllTasksCompleted(): bool
    {
        $isWeekend = in_array($this->date->dayOfWeek, [0, 6]); // 0=週日, 6=週六
        
        if ($isWeekend) {
            return $this->task_meal && $this->task_walk && 
                   $this->task_no_snack && $this->task_no_sugar;
        } else {
            return $this->task_meal && $this->task_walk && 
                   $this->task_no_snack && $this->task_sleep;
        }
    }
}
```

### 3. 建立 `Achievement` 模型

**檔案**：`app/Models/Achievement.php`

```php
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

    // 檢查用戶是否已解鎖此成就
    public function isUnlockedBy(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }
}
```

### 4. 建立 `UserAchievement` 模型（可選，用於直接存取）

**檔案**：`app/Models/UserAchievement.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAchievement extends Model
{
    use HasFactory;

    protected $table = 'user_achievements';

    protected $fillable = [
        'user_id',
        'achievement_id',
        'unlocked_at',
        'weight_at_unlock',
    ];

    protected function casts(): array
    {
        return [
            'unlocked_at' => 'datetime',
            'weight_at_unlock' => 'decimal:1',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function achievement(): BelongsTo
    {
        return $this->belongsTo(Achievement::class);
    }
}
```

### 5. 建立 `Reward` 模型

**檔案**：`app/Models/Reward.php`

```php
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
```

---

## 🎯 服務類別設計

### 1. `DailyTaskService` - 每日任務服務

**檔案**：`app/Services/DailyTaskService.php`

**主要方法**：

```php
<?php

namespace App\Services;

use App\Models\User;
use App\Models\DailyLog;
use Carbon\Carbon;

class DailyTaskService
{
    /**
     * 取得今日任務清單（依週幾判斷工作日/假日）
     */
    public function getTodayTasks(Carbon $date = null): array
    {
        $date = $date ?? Carbon::today();
        $isWeekend = in_array($date->dayOfWeek, [0, 6]); // 0=週日, 6=週六
        
        if ($isWeekend) {
            return [
                ['key' => 'task_meal', 'name' => '只吃 2 餐', 'points' => 10],
                ['key' => 'task_walk', 'name' => '散步 1 次（可陪家人）', 'points' => 20],
                ['key' => 'task_no_snack', 'name' => '不吃宵夜', 'points' => 10],
                ['key' => 'task_no_sugar', 'name' => '不喝含糖飲料', 'points' => 10],
            ];
        } else {
            return [
                ['key' => 'task_meal', 'name' => '只吃 1 餐晚餐', 'points' => 10],
                ['key' => 'task_walk', 'name' => '中午散步 30 分鐘', 'points' => 20],
                ['key' => 'task_no_snack', 'name' => '不吃宵夜', 'points' => 10],
                ['key' => 'task_sleep', 'name' => '早點睡（11:00 前）', 'points' => 10],
            ];
        }
    }

    /**
     * 計算每日任務積分
     */
    public function calculateDailyPoints(DailyLog $dailyLog): int
    {
        $isWeekend = in_array($dailyLog->date->dayOfWeek, [0, 6]);
        $points = 0;
        
        if ($isWeekend) {
            if ($dailyLog->task_meal) $points += 10;
            if ($dailyLog->task_walk) $points += 20;
            if ($dailyLog->task_no_snack) $points += 10;
            if ($dailyLog->task_no_sugar) $points += 10;
        } else {
            if ($dailyLog->task_meal) $points += 10;
            if ($dailyLog->task_walk) $points += 20;
            if ($dailyLog->task_no_snack) $points += 10;
            if ($dailyLog->task_sleep) $points += 10;
        }
        
        return $points;
    }

    /**
     * 計算週任務積分
     */
    public function calculateWeeklyPoints(User $user, Carbon $weekStart): int
    {
        $weekEnd = $weekStart->copy()->endOfWeek();
        $points = 0;
        
        // 工作日任務（週一到週五）
        $workdayLogs = $user->dailyLogs()
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->whereRaw('DAYOFWEEK(date) BETWEEN 2 AND 6') // 週一到週五
            ->get();
        
        $completedWorkdays = $workdayLogs->filter(function ($log) {
            return $log->isAllTasksCompleted();
        })->count();
        
        if ($completedWorkdays >= 5) {
            $points += 100;
        }
        
        // 假日任務（週六、週日）
        $weekendLogs = $user->dailyLogs()
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->whereRaw('DAYOFWEEK(date) IN (1, 7)') // 週日、週六
            ->get();
        
        $completedWeekends = $weekendLogs->filter(function ($log) {
            return $log->isAllTasksCompleted();
        })->count();
        
        if ($completedWeekends >= 2) {
            $points += 50;
        }
        
        // 體重下降 0.5kg
        $startWeight = $user->dailyLogs()
            ->where('date', $weekStart->format('Y-m-d'))
            ->first()?->weight;
        $endWeight = $user->dailyLogs()
            ->where('date', $weekEnd->format('Y-m-d'))
            ->first()?->weight;
        
        if ($startWeight && $endWeight && ($startWeight - $endWeight) >= 0.5) {
            $points += 200;
        }
        
        return $points;
    }
}
```

### 2. `PointsService` - 積分服務

**檔案**：`app/Services/PointsService.php`

```php
<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class PointsService
{
    /**
     * 增加積分
     */
    public function addPoints(User $user, int $points, string $source = 'task'): void
    {
        DB::transaction(function () use ($user, $points) {
            $user->increment('total_points', $points);
            $user->increment('available_points', $points);
        });
    }

    /**
     * 扣除積分（兌換獎勵）
     */
    public function deductPoints(User $user, int $points): bool
    {
        if ($user->available_points < $points) {
            return false; // 積分不足
        }
        
        DB::transaction(function () use ($user, $points) {
            $user->decrement('available_points', $points);
        });
        
        return true;
    }

    /**
     * 取得可用積分
     */
    public function getAvailablePoints(User $user): int
    {
        return $user->available_points;
    }

    /**
     * 取得總積分
     */
    public function getTotalPoints(User $user): int
    {
        return $user->total_points;
    }
}
```

### 3. `AchievementService` - 成就服務

**檔案**：`app/Services/AchievementService.php`

```php
<?php

namespace App\Services;

use App\Models\User;
use App\Models\Achievement;
use App\Models\DailyLog;
use App\Services\PointsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AchievementService
{
    public function __construct(
        private PointsService $pointsService
    ) {}

    /**
     * 檢查體重里程碑成就
     */
    public function checkWeightMilestones(User $user): array
    {
        $unlocked = [];
        $currentWeight = $user->current_weight;
        
        if (!$currentWeight) {
            return $unlocked;
        }
        
        $weightMilestones = Achievement::where('type', 'weight_milestone')
            ->where('requirement_value', '>=', $currentWeight)
            ->get();
        
        foreach ($weightMilestones as $achievement) {
            if (!$achievement->isUnlockedBy($user)) {
                $this->unlockAchievement($user, $achievement, $currentWeight);
                $unlocked[] = $achievement;
            }
        }
        
        return $unlocked;
    }

    /**
     * 檢查特殊成就
     */
    public function checkSpecialAchievements(User $user): array
    {
        $unlocked = [];
        
        // 完美一週：連續 7 天完成所有任務
        $this->checkPerfectWeek($user, $unlocked);
        
        // 完美一月：連續 30 天完成所有任務
        $this->checkPerfectMonth($user, $unlocked);
        
        // 週末戰士：連續 4 個週末都完成任務
        $this->checkWeekendWarrior($user, $unlocked);
        
        // 省錢達人：累積省下 NT$50,000
        $this->checkMoneySaver($user, $unlocked);
        
        // 散步狂人：累積散步 100 次
        $this->checkWalkMaster($user, $unlocked);
        
        // 早睡冠軍：連續 30 天 11:00 前睡覺
        $this->checkEarlyBird($user, $unlocked);
        
        // 斷食大師：連續 30 天只吃 1 餐
        $this->checkFastingMaster($user, $unlocked);
        
        return $unlocked;
    }

    /**
     * 解鎖成就
     */
    public function unlockAchievement(User $user, Achievement $achievement, ?float $weightAtUnlock = null): void
    {
        DB::transaction(function () use ($user, $achievement, $weightAtUnlock) {
            // 檢查是否已解鎖
            if ($achievement->isUnlockedBy($user)) {
                return;
            }
            
            // 記錄成就解鎖
            $user->achievements()->attach($achievement->id, [
                'unlocked_at' => now(),
                'weight_at_unlock' => $weightAtUnlock,
            ]);
            
            // 增加獎勵積分
            if ($achievement->points_reward > 0) {
                $this->pointsService->addPoints($user, $achievement->points_reward, 'achievement');
            }
        });
    }

    /**
     * 檢查完美一週成就
     */
    private function checkPerfectWeek(User $user, array &$unlocked): void
    {
        $achievement = Achievement::where('code', 'perfect_week')->first();
        if (!$achievement || $achievement->isUnlockedBy($user)) {
            return;
        }
        
        $sevenDaysAgo = Carbon::today()->subDays(6);
        $logs = $user->dailyLogs()
            ->where('date', '>=', $sevenDaysAgo)
            ->orderBy('date')
            ->get();
        
        if ($logs->count() === 7 && $logs->every(fn($log) => $log->isAllTasksCompleted())) {
            $this->unlockAchievement($user, $achievement);
            $unlocked[] = $achievement;
        }
    }

    /**
     * 檢查完美一月成就
     */
    private function checkPerfectMonth(User $user, array &$unlocked): void
    {
        $achievement = Achievement::where('code', 'perfect_month')->first();
        if (!$achievement || $achievement->isUnlockedBy($user)) {
            return;
        }
        
        $thirtyDaysAgo = Carbon::today()->subDays(29);
        $logs = $user->dailyLogs()
            ->where('date', '>=', $thirtyDaysAgo)
            ->orderBy('date')
            ->get();
        
        if ($logs->count() === 30 && $logs->every(fn($log) => $log->isAllTasksCompleted())) {
            $this->unlockAchievement($user, $achievement);
            $unlocked[] = $achievement;
        }
    }

    /**
     * 檢查週末戰士成就
     */
    private function checkWeekendWarrior(User $user, array &$unlocked): void
    {
        $achievement = Achievement::where('code', 'weekend_warrior')->first();
        if (!$achievement || $achievement->isUnlockedBy($user)) {
            return;
        }
        
        // 檢查最近 4 個週末
        $weekends = [];
        $date = Carbon::today();
        while (count($weekends) < 4) {
            if (in_array($date->dayOfWeek, [0, 6])) {
                $weekends[] = $date->format('Y-m-d');
            }
            $date->subDay();
        }
        
        $logs = $user->dailyLogs()
            ->whereIn('date', $weekends)
            ->get();
        
        if ($logs->count() === 4 && $logs->every(fn($log) => $log->isAllTasksCompleted())) {
            $this->unlockAchievement($user, $achievement);
            $unlocked[] = $achievement;
        }
    }

    /**
     * 檢查省錢達人成就
     */
    private function checkMoneySaver(User $user, array &$unlocked): void
    {
        $achievement = Achievement::where('code', 'money_saver')->first();
        if (!$achievement || $achievement->isUnlockedBy($user)) {
            return;
        }
        
        if ($user->potential_savings >= 50000) {
            $this->unlockAchievement($user, $achievement);
            $unlocked[] = $achievement;
        }
    }

    /**
     * 檢查散步狂人成就
     */
    private function checkWalkMaster(User $user, array &$unlocked): void
    {
        $achievement = Achievement::where('code', 'walk_master')->first();
        if (!$achievement || $achievement->isUnlockedBy($user)) {
            return;
        }
        
        $walkCount = $user->dailyLogs()
            ->where('task_walk', true)
            ->count();
        
        if ($walkCount >= 100) {
            $this->unlockAchievement($user, $achievement);
            $unlocked[] = $achievement;
        }
    }

    /**
     * 檢查早睡冠軍成就
     */
    private function checkEarlyBird(User $user, array &$unlocked): void
    {
        $achievement = Achievement::where('code', 'early_bird')->first();
        if (!$achievement || $achievement->isUnlockedBy($user)) {
            return;
        }
        
        $thirtyDaysAgo = Carbon::today()->subDays(29);
        $logs = $user->dailyLogs()
            ->where('date', '>=', $thirtyDaysAgo)
            ->where('task_sleep', true)
            ->count();
        
        if ($logs >= 30) {
            $this->unlockAchievement($user, $achievement);
            $unlocked[] = $achievement;
        }
    }

    /**
     * 檢查斷食大師成就
     */
    private function checkFastingMaster(User $user, array &$unlocked): void
    {
        $achievement = Achievement::where('code', 'fasting_master')->first();
        if (!$achievement || $achievement->isUnlockedBy($user)) {
            return;
        }
        
        $thirtyDaysAgo = Carbon::today()->subDays(29);
        $logs = $user->dailyLogs()
            ->where('date', '>=', $thirtyDaysAgo)
            ->where('task_meal', true)
            ->whereRaw('DAYOFWEEK(date) BETWEEN 2 AND 6') // 只計算工作日
            ->count();
        
        if ($logs >= 30) {
            $this->unlockAchievement($user, $achievement);
            $unlocked[] = $achievement;
        }
    }
}
```

---

## 🎮 控制器設計

### 1. `DailyLogController` - 每日記錄控制器

**檔案**：`app/Http/Controllers/DailyLogController.php`

**路由**（在 `routes/web.php` 中）：
```php
Route::middleware(['auth'])->group(function () {
    Route::resource('daily-logs', DailyLogController::class);
    Route::post('/daily-logs/{dailyLog}/toggle-task', [DailyLogController::class, 'toggleTask'])->name('daily-logs.toggle-task');
});
```

**控制器實作**：
```php
<?php

namespace App\Http\Controllers;

use App\Models\DailyLog;
use App\Services\DailyTaskService;
use App\Services\PointsService;
use App\Services\AchievementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DailyLogController extends Controller
{
    public function __construct(
        private DailyTaskService $taskService,
        private PointsService $pointsService,
        private AchievementService $achievementService
    ) {}

    /**
     * 顯示今日任務儀表板
     */
    public function index()
    {
        $user = auth()->user();
        $today = Carbon::today();
        
        $dailyLog = $user->dailyLogs()
            ->where('date', $today)
            ->first();
        
        $tasks = $this->taskService->getTodayTasks($today);
        
        // 計算已完成任務數
        $completedTasks = 0;
        if ($dailyLog) {
            foreach ($tasks as $task) {
                if ($dailyLog->{$task['key']}) {
                    $completedTasks++;
                }
            }
        }
        
        return view('daily-log.index', [
            'dailyLog' => $dailyLog,
            'tasks' => $tasks,
            'completedTasks' => $completedTasks,
            'today' => $today,
        ]);
    }

    /**
     * 建立或更新每日記錄
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'weight' => 'nullable|numeric|min:0|max:300',
            'task_meal' => 'boolean',
            'task_walk' => 'boolean',
            'task_no_snack' => 'boolean',
            'task_sleep' => 'boolean',
            'task_no_sugar' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        return DB::transaction(function () use ($validated) {
            $user = auth()->user();
            $date = Carbon::parse($validated['date']);
            
            // 取得或建立每日記錄
            $dailyLog = $user->dailyLogs()->firstOrNew([
                'date' => $date,
            ]);
            
            // 更新任務狀態
            $dailyLog->fill([
                'weight' => $validated['weight'] ?? $dailyLog->weight,
                'task_meal' => $validated['task_meal'] ?? false,
                'task_walk' => $validated['task_walk'] ?? false,
                'task_no_snack' => $validated['task_no_snack'] ?? false,
                'task_sleep' => $validated['task_sleep'] ?? false,
                'task_no_sugar' => $validated['task_no_sugar'] ?? false,
                'notes' => $validated['notes'] ?? $dailyLog->notes,
            ]);
            
            // 計算每日積分
            $dailyPoints = $this->taskService->calculateDailyPoints($dailyLog);
            $dailyLog->daily_points = $dailyPoints;
            
            // 如果是週末，計算週任務積分
            if ($date->isWeekend()) {
                $weekStart = $date->copy()->startOfWeek();
                $weeklyPoints = $this->taskService->calculateWeeklyPoints($user, $weekStart);
                $dailyLog->weekly_points = $weeklyPoints;
            }
            
            $dailyLog->save();
            
            // 增加積分
            $oldPoints = $dailyLog->getOriginal('daily_points') ?? 0;
            $pointsDiff = $dailyPoints - $oldPoints;
            if ($pointsDiff > 0) {
                $this->pointsService->addPoints($user, $pointsDiff, 'daily_task');
            }
            
            // 更新連續達成天數
            $this->updateStreak($user);
            
            // 檢查成就
            $unlockedAchievements = [];
            if ($dailyLog->isAllTasksCompleted()) {
                $unlockedAchievements = $this->achievementService->checkSpecialAchievements($user);
            }
            
            // 如果有體重記錄，檢查體重里程碑
            if ($dailyLog->weight) {
                $weightAchievements = $this->achievementService->checkWeightMilestones($user);
                $unlockedAchievements = array_merge($unlockedAchievements, $weightAchievements);
            }
            
            return response()->json([
                'success' => true,
                'dailyLog' => $dailyLog,
                'dailyPoints' => $dailyPoints,
                'unlockedAchievements' => $unlockedAchievements,
            ]);
        });
    }

    /**
     * 切換任務狀態（AJAX）
     */
    public function toggleTask(DailyLog $dailyLog, Request $request)
    {
        $validated = $request->validate([
            'task' => 'required|in:task_meal,task_walk,task_no_snack,task_sleep,task_no_sugar',
        ]);
        
        $task = $validated['task'];
        $dailyLog->{$task} = !$dailyLog->{$task};
        
        // 重新計算積分
        $dailyPoints = $this->taskService->calculateDailyPoints($dailyLog);
        $dailyLog->daily_points = $dailyPoints;
        $dailyLog->save();
        
        // 更新積分
        $this->pointsService->addPoints(auth()->user(), $dailyPoints - ($dailyLog->getOriginal('daily_points') ?? 0), 'daily_task');
        
        return response()->json([
            'success' => true,
            'dailyLog' => $dailyLog,
            'dailyPoints' => $dailyPoints,
        ]);
    }

    /**
     * 更新連續達成天數
     */
    private function updateStreak($user): void
    {
        $streak = 0;
        $date = Carbon::today();
        
        while (true) {
            $log = $user->dailyLogs()
                ->where('date', $date->format('Y-m-d'))
                ->first();
            
            if (!$log || !$log->isAllTasksCompleted()) {
                break;
            }
            
            $streak++;
            $date->subDay();
        }
        
        $user->current_streak = $streak;
        if ($streak > $user->longest_streak) {
            $user->longest_streak = $streak;
        }
        $user->save();
    }
}
```

### 2. `AchievementController` - 成就控制器

**檔案**：`app/Http/Controllers/AchievementController.php`

**路由**：
```php
Route::get('/achievements', [AchievementController::class, 'index'])->name('achievements.index');
Route::get('/achievements/{achievement}', [AchievementController::class, 'show'])->name('achievements.show');
```

**控制器實作**：
```php
<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use Illuminate\Http\Request;

class AchievementController extends Controller
{
    /**
     * 顯示成就牆
     */
    public function index()
    {
        $user = auth()->user();
        
        $achievements = Achievement::orderBy('sort_order')
            ->orderBy('type')
            ->get()
            ->groupBy('type');
        
        $unlockedAchievementIds = $user->achievements()->pluck('achievements.id')->toArray();
        
        return view('achievements.index', [
            'achievements' => $achievements,
            'unlockedAchievementIds' => $unlockedAchievementIds,
        ]);
    }

    /**
     * 顯示成就詳情
     */
    public function show(Achievement $achievement)
    {
        $user = auth()->user();
        $isUnlocked = $achievement->isUnlockedBy($user);
        
        $userAchievement = null;
        if ($isUnlocked) {
            $userAchievement = $user->achievements()
                ->where('achievements.id', $achievement->id)
                ->first();
        }
        
        return view('achievements.show', [
            'achievement' => $achievement,
            'isUnlocked' => $isUnlocked,
            'userAchievement' => $userAchievement,
        ]);
    }
}
```

### 3. `RewardController` - 獎勵控制器

**檔案**：`app/Http/Controllers/RewardController.php`

**路由**：
```php
Route::get('/rewards', [RewardController::class, 'index'])->name('rewards.index');
Route::post('/rewards', [RewardController::class, 'store'])->name('rewards.store');
Route::get('/rewards/history', [RewardController::class, 'history'])->name('rewards.history');
```

**控制器實作**：
```php
<?php

namespace App\Http\Controllers;

use App\Models\Reward;
use App\Services\PointsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RewardController extends Controller
{
    public function __construct(
        private PointsService $pointsService
    ) {}

    /**
     * 顯示獎勵商店
     */
    public function index()
    {
        $user = auth()->user();
        
        $rewards = [
            [
                'type' => 'indulgence_meal',
                'name' => '放縱餐券',
                'points' => 500,
                'description' => '週末可以多吃一餐「放縱餐」，不扣分',
            ],
            [
                'type' => 'small_reward',
                'name' => '小確幸',
                'points' => 1000,
                'description' => '買一件想要的東西（NT$500 以內）',
            ],
            [
                'type' => 'family_time',
                'name' => '親子時光',
                'points' => 2000,
                'description' => '帶家人去吃好料',
            ],
            [
                'type' => 'self_reward',
                'name' => '犒賞自己',
                'points' => 3000,
                'description' => '買一個想要的東西（NT$1,000 以內）',
            ],
            [
                'type' => 'big_reward',
                'name' => '大獎勵',
                'points' => 5000,
                'description' => '買一個想要很久的東西（NT$2,000 以內）',
            ],
        ];
        
        return view('rewards.index', [
            'rewards' => $rewards,
            'availablePoints' => $user->available_points,
        ]);
    }

    /**
     * 兌換獎勵
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'reward_type' => 'required|string',
            'reward_name' => 'required|string',
            'points_spent' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);
        
        return DB::transaction(function () use ($validated) {
            $user = auth()->user();
            
            // 檢查積分是否足夠
            if ($user->available_points < $validated['points_spent']) {
                return back()->withErrors(['points' => '積分不足']);
            }
            
            // 扣除積分
            if (!$this->pointsService->deductPoints($user, $validated['points_spent'])) {
                return back()->withErrors(['points' => '積分扣除失敗']);
            }
            
            // 記錄兌換
            $reward = Reward::create([
                'user_id' => $user->id,
                'reward_type' => $validated['reward_type'],
                'reward_name' => $validated['reward_name'],
                'points_spent' => $validated['points_spent'],
                'redeemed_at' => now(),
                'notes' => $validated['notes'] ?? null,
            ]);
            
            return redirect()->route('rewards.history')
                ->with('success', '獎勵兌換成功！');
        });
    }

    /**
     * 顯示兌換歷史
     */
    public function history()
    {
        $user = auth()->user();
        
        $rewards = $user->rewards()
            ->orderBy('redeemed_at', 'desc')
            ->paginate(20);
        
        return view('rewards.history', [
            'rewards' => $rewards,
        ]);
    }
}
```

---

## 🎨 視圖設計

### 1. 遊戲化儀表板

**檔案**：`resources/views/dashboard.blade.php`

**主要區塊**：

1. **積分與連續達成卡片**
```blade
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <!-- 可用積分 -->
    <div class="bg-gradient-to-r from-yellow-400 to-orange-500 rounded-xl p-6 text-white">
        <div class="text-sm font-medium mb-1">可用積分</div>
        <div class="text-3xl font-bold">{{ auth()->user()->available_points }}</div>
    </div>
    
    <!-- 連續達成 -->
    <div class="bg-gradient-to-r from-red-400 to-pink-500 rounded-xl p-6 text-white">
        <div class="text-sm font-medium mb-1">連續達成</div>
        <div class="text-3xl font-bold">{{ auth()->user()->current_streak }} 天</div>
    </div>
    
    <!-- 潛在節省 -->
    <div class="bg-gradient-to-r from-green-400 to-blue-500 rounded-xl p-6 text-white">
        <div class="text-sm font-medium mb-1">潛在節省</div>
        <div class="text-3xl font-bold">NT$ {{ number_format(auth()->user()->potential_savings) }}</div>
    </div>
</div>
```

2. **今日任務進度**
```blade
@php
    $today = \Carbon\Carbon::today();
    $dailyLog = auth()->user()->dailyLogs()->where('date', $today)->first();
    $taskService = app(\App\Services\DailyTaskService::class);
    $tasks = $taskService->getTodayTasks($today);
    $completedTasks = $dailyLog ? collect($tasks)->filter(fn($t) => $dailyLog->{$t['key']})->count() : 0;
    $totalTasks = count($tasks);
    $progress = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;
@endphp

<div class="bg-white rounded-xl shadow-lg p-6 mb-6">
    <h3 class="text-lg font-bold mb-4">📅 今日任務</h3>
    <div class="mb-4">
        <div class="flex justify-between text-sm mb-2">
            <span>進度</span>
            <span>{{ $completedTasks }} / {{ $totalTasks }}</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-4">
            <div class="bg-indigo-600 h-4 rounded-full transition-all duration-300" 
                 style="width: {{ $progress }}%"></div>
        </div>
    </div>
    <a href="{{ route('daily-logs.index') }}" 
       class="inline-block px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
        查看任務詳情
    </a>
</div>
```

3. **最近解鎖的成就**
```blade
@php
    $recentAchievements = auth()->user()->achievements()
        ->orderByPivot('unlocked_at', 'desc')
        ->limit(3)
        ->get();
@endphp

@if($recentAchievements->count() > 0)
<div class="bg-white rounded-xl shadow-lg p-6 mb-6">
    <h3 class="text-lg font-bold mb-4">🏆 最近解鎖的成就</h3>
    <div class="grid grid-cols-3 gap-4">
        @foreach($recentAchievements as $achievement)
            <div class="text-center">
                <div class="text-4xl mb-2">{{ $achievement->icon }}</div>
                <div class="text-sm font-medium">{{ $achievement->name }}</div>
            </div>
        @endforeach
    </div>
    <a href="{{ route('achievements.index') }}" 
       class="mt-4 inline-block text-indigo-600 hover:text-indigo-800">
        查看所有成就 →
    </a>
</div>
@endif
```

4. **激勵語錄**
```blade
@php
    $user = auth()->user();
    $currentWeight = $user->current_weight;
    $startWeight = $user->start_weight;
    $weightLost = $startWeight && $currentWeight ? $startWeight - $currentWeight : 0;
    $potentialSavings = $user->potential_savings;
    
    $motivationalQuotes = [];
    if ($weightLost < 3) {
        $motivationalQuotes = [
            '每走一步，都在省錢！',
            '萬事起頭難，你已經開始了！',
            '未來的你會感謝現在的自己！',
        ];
    } elseif ($weightLost < 13) {
        $motivationalQuotes = [
            "你已經減掉 {$weightLost} 公斤，繼續加油！",
            "你已經省下 NT$" . number_format($potentialSavings) . "，真划算！",
            '堅持就是勝利，你做得很好！',
        ];
    } else {
        $motivationalQuotes = [
            '勝利在望！繼續堅持！',
            '你已經走了這麼遠，不要放棄！',
            '想像達標那一刻的成就感！',
        ];
    }
    $quote = $motivationalQuotes[array_rand($motivationalQuotes)];
@endphp

<div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl p-6 border border-purple-200">
    <div class="flex items-center gap-3">
        <div class="text-3xl">💬</div>
        <div>
            <div class="text-sm text-purple-600 font-medium mb-1">今日激勵</div>
            <div class="text-lg font-bold text-purple-800">{{ $quote }}</div>
        </div>
    </div>
</div>
```

### 2. 每日任務介面

**檔案**：`resources/views/daily-log/index.blade.php`

**主要功能**：
- 顯示今日任務清單（依週幾自動判斷）
- 任務勾選框（使用 Alpine.js 處理 AJAX 更新）
- 即時顯示積分變化
- 顯示連續達成天數
- 可記錄體重和備註

**關鍵程式碼片段**：
```blade
<div x-data="dailyLogData()" class="space-y-4">
    @foreach($tasks as $task)
        <div class="flex items-center justify-between p-4 bg-white rounded-lg shadow">
            <div class="flex items-center gap-3">
                <input type="checkbox" 
                       x-model="tasks['{{ $task['key'] }}']"
                       @change="toggleTask('{{ $task['key'] }}')"
                       class="w-5 h-5 text-indigo-600 rounded">
                <div>
                    <div class="font-medium">{{ $task['name'] }}</div>
                    <div class="text-sm text-gray-500">+{{ $task['points'] }} 積分</div>
                </div>
            </div>
        </div>
    @endforeach
    
    <div class="mt-6 p-4 bg-indigo-50 rounded-lg">
        <div class="text-sm text-indigo-600 mb-1">今日得分</div>
        <div class="text-2xl font-bold text-indigo-800" x-text="dailyPoints + ' / 50'"></div>
    </div>
</div>

<script>
function dailyLogData() {
    return {
        tasks: @json($dailyLog ? [
            'task_meal' => $dailyLog->task_meal,
            'task_walk' => $dailyLog->task_walk,
            'task_no_snack' => $dailyLog->task_no_snack,
            'task_sleep' => $dailyLog->task_sleep,
            'task_no_sugar' => $dailyLog->task_no_sugar,
        ] : []),
        dailyPoints: {{ $dailyLog->daily_points ?? 0 }},
        
        async toggleTask(taskKey) {
            // AJAX 更新任務狀態
            const response = await fetch(`/daily-logs/{{ $dailyLog->id ?? 'new' }}/toggle-task`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ task: taskKey }),
            });
            
            const data = await response.json();
            this.dailyPoints = data.dailyPoints;
        }
    }
}
</script>
```

### 3. 成就牆

**檔案**：`resources/views/achievements/index.blade.php`

**設計要點**：
- 使用網格佈局顯示所有成就
- 已解鎖的成就顯示彩色，未解鎖的顯示灰色
- 點擊成就可查看詳情
- 使用 Tailwind CSS 實現卡片效果

**關鍵程式碼**：
```blade
<div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
    @foreach($achievements['weight_milestone'] ?? [] as $achievement)
        @php
            $isUnlocked = in_array($achievement->id, $unlockedAchievementIds);
        @endphp
        <a href="{{ route('achievements.show', $achievement) }}" 
           class="block p-4 rounded-xl transition-all hover:scale-105 {{ $isUnlocked ? 'bg-gradient-to-br from-yellow-400 to-orange-500 text-white shadow-lg' : 'bg-gray-200 text-gray-400' }}">
            <div class="text-4xl text-center mb-2">{{ $achievement->icon }}</div>
            <div class="text-sm font-medium text-center">{{ $achievement->name }}</div>
            @if($isUnlocked)
                <div class="text-xs text-center mt-1 opacity-75">已解鎖</div>
            @else
                <div class="text-xs text-center mt-1">未解鎖</div>
            @endif
        </a>
    @endforeach
</div>
```

---

## 🔗 與現有功能整合

### 1. 整合體重記錄功能

**檔案**：`app/Http/Controllers/WeightController.php`

**在 `store()` 方法中新增**：
```php
use App\Services\AchievementService;

public function __construct(
    private AchievementService $achievementService
) {}

public function store(StoreWeightRequest $request)
{
    // ... 現有的體重記錄邏輯 ...
    
    // 檢查體重里程碑成就
    $unlockedAchievements = $this->achievementService->checkWeightMilestones($user);
    
    // 如果有解鎖成就，在 session 中記錄，以便顯示通知
    if (count($unlockedAchievements) > 0) {
        session()->flash('unlocked_achievements', $unlockedAchievements);
    }
    
    // ... 返回回應 ...
}
```

### 2. 整合體重目標功能

在儀表板中同時顯示：
- 體重目標進度（現有功能）
- 遊戲化進度（新功能）
- 兩者可以並列顯示，讓用戶看到雙重進度

---

## 📊 成就資料 Seeder

**檔案**：`database/seeders/AchievementSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\Achievement;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    public function run(): void
    {
        // 體重里程碑成就
        $weightMilestones = [
            ['code' => 'weight_107', 'name' => '起步者', 'icon' => '🎖️', 'requirement_value' => 107.0, 'points_reward' => 0, 'sort_order' => 1],
            ['code' => 'weight_105', 'name' => '認真了', 'icon' => '🎖️', 'requirement_value' => 105.0, 'points_reward' => 0, 'sort_order' => 2],
            ['code' => 'weight_100', 'name' => '破百', 'icon' => '🏅', 'requirement_value' => 100.0, 'points_reward' => 0, 'sort_order' => 3],
            ['code' => 'weight_95', 'name' => '過半', 'icon' => '🏅', 'requirement_value' => 95.0, 'points_reward' => 0, 'sort_order' => 4],
            ['code' => 'weight_90', 'name' => 'BMI 降級', 'icon' => '🏆', 'requirement_value' => 90.0, 'points_reward' => 0, 'sort_order' => 5],
            ['code' => 'weight_85', 'name' => '接近目標', 'icon' => '🏆', 'requirement_value' => 85.0, 'points_reward' => 0, 'sort_order' => 6],
            ['code' => 'weight_80', 'name' => '終極勝利', 'icon' => '👑', 'requirement_value' => 80.0, 'points_reward' => 0, 'sort_order' => 7],
        ];
        
        foreach ($weightMilestones as $milestone) {
            Achievement::create([
                'code' => $milestone['code'],
                'name' => $milestone['name'],
                'description' => $this->getWeightMilestoneDescription($milestone['requirement_value']),
                'icon' => $milestone['icon'],
                'type' => 'weight_milestone',
                'requirement_value' => $milestone['requirement_value'],
                'points_reward' => $milestone['points_reward'],
                'sort_order' => $milestone['sort_order'],
            ]);
        }
        
        // 特殊成就
        $specialAchievements = [
            ['code' => 'perfect_week', 'name' => '完美一週', 'icon' => '⭐', 'description' => '連續 7 天完成所有每日任務', 'points_reward' => 100],
            ['code' => 'perfect_month', 'name' => '完美一月', 'icon' => '🌟', 'description' => '連續 30 天完成所有每日任務', 'points_reward' => 500],
            ['code' => 'weekend_warrior', 'name' => '週末戰士', 'icon' => '💪', 'description' => '連續 4 個週末都完成任務', 'points_reward' => 200],
            ['code' => 'money_saver', 'name' => '省錢達人', 'icon' => '💰', 'description' => '累積省下 NT$50,000', 'points_reward' => 300],
            ['code' => 'walk_master', 'name' => '散步狂人', 'icon' => '🚶', 'description' => '累積散步 100 次', 'points_reward' => 200],
            ['code' => 'early_bird', 'name' => '早睡冠軍', 'icon' => '😴', 'description' => '連續 30 天 11:00 前睡覺', 'points_reward' => 200],
            ['code' => 'fasting_master', 'name' => '斷食大師', 'icon' => '🍽️', 'description' => '連續 30 天只吃 1 餐', 'points_reward' => 300],
        ];
        
        foreach ($specialAchievements as $achievement) {
            Achievement::create([
                'code' => $achievement['code'],
                'name' => $achievement['name'],
                'description' => $achievement['description'],
                'icon' => $achievement['icon'],
                'type' => 'special',
                'requirement_value' => null,
                'points_reward' => $achievement['points_reward'],
                'sort_order' => 100, // 特殊成就排在後面
            ]);
        }
    }
    
    private function getWeightMilestoneDescription(float $weight): string
    {
        $descriptions = [
            107.0 => '萬事起頭難，你已經邁出第一步！',
            105.0 => '連續達成目標，證明你是認真的！',
            100.0 => '重大里程碑！體重回到兩位數！',
            95.0 => '已經完成一半的旅程！',
            90.0 => 'BMI 從肥胖降級到過重，健康大躍進！',
            85.0 => '勝利在望！再堅持一下！',
            80.0 => '恭喜！你靠自己的意志力達成目標！',
        ];
        
        return $descriptions[$weight] ?? '';
    }
}
```

**在 `DatabaseSeeder.php` 中呼叫**：
```php
$this->call([
    AchievementSeeder::class,
]);
```

---

## 🧪 測試建議

### 1. Feature 測試

**檔案**：`tests/Feature/DailyLogTest.php`

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\DailyLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_daily_log(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->post('/daily-logs', [
                'date' => today()->format('Y-m-d'),
                'task_meal' => true,
                'task_walk' => true,
                'task_no_snack' => true,
                'task_sleep' => true,
            ]);
        
        $response->assertStatus(200);
        $this->assertDatabaseHas('daily_logs', [
            'user_id' => $user->id,
            'date' => today()->format('Y-m-d'),
        ]);
    }
    
    public function test_daily_points_are_calculated_correctly(): void
    {
        $user = User::factory()->create();
        
        $this->actingAs($user)
            ->post('/daily-logs', [
                'date' => today()->format('Y-m-d'),
                'task_meal' => true,
                'task_walk' => true,
                'task_no_snack' => true,
                'task_sleep' => true,
            ]);
        
        $dailyLog = DailyLog::where('user_id', $user->id)
            ->where('date', today())
            ->first();
        
        $this->assertEquals(50, $dailyLog->daily_points);
    }
}
```

### 2. 成就解鎖測試

**檔案**：`tests/Feature/AchievementTest.php`

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Achievement;
use App\Models\Weight;
use App\Services\AchievementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AchievementTest extends TestCase
{
    use RefreshDatabase;

    public function test_weight_milestone_achievement_is_unlocked(): void
    {
        $user = User::factory()->create(['start_weight' => 108.0]);
        $achievement = Achievement::factory()->create([
            'code' => 'weight_107',
            'type' => 'weight_milestone',
            'requirement_value' => 107.0,
        ]);
        
        // 記錄體重 107kg
        Weight::factory()->create([
            'user_id' => $user->id,
            'weight' => 107.0,
            'record_at' => today(),
        ]);
        
        $service = app(AchievementService::class);
        $unlocked = $service->checkWeightMilestones($user);
        
        $this->assertCount(1, $unlocked);
        $this->assertTrue($user->achievements()->where('code', 'weight_107')->exists());
    }
}
```

---

## 📝 實作檢查清單

### Phase 1：資料庫與模型
- [ ] 建立 5 個遷移檔案
- [ ] 執行遷移
- [ ] 建立 4 個新模型（DailyLog, Achievement, UserAchievement, Reward）
- [ ] 擴展 User 模型（新增欄位、關聯、存取器）
- [ ] 建立 AchievementSeeder
- [ ] 執行 Seeder

### Phase 2：服務類別
- [ ] 建立 DailyTaskService
- [ ] 建立 PointsService
- [ ] 建立 AchievementService
- [ ] 測試服務類別方法

### Phase 3：控制器
- [ ] 建立 DailyLogController
- [ ] 建立 AchievementController
- [ ] 建立 RewardController
- [ ] 新增路由
- [ ] 整合 WeightController（成就檢查）

### Phase 4：視圖
- [ ] 重新設計 dashboard.blade.php
- [ ] 建立 daily-log/index.blade.php
- [ ] 建立 achievements/index.blade.php
- [ ] 建立 achievements/show.blade.php
- [ ] 建立 rewards/index.blade.php
- [ ] 建立 rewards/history.blade.php

### Phase 5：測試與優化
- [ ] 撰寫 Feature 測試
- [ ] 新增資料庫索引
- [ ] 優化查詢（eager loading）
- [ ] UI/UX 調整
- [ ] 響應式設計檢查

---

## 🚀 開始實作

請按照以下順序進行：

1. **先建立資料庫結構**（遷移檔案）
2. **建立模型和關聯**
3. **建立服務類別**（核心邏輯）
4. **建立控制器和路由**
5. **建立視圖**
6. **整合現有功能**
7. **測試和優化**

每個階段完成後，請測試功能是否正常運作，再進行下一階段。

---

## 📚 參考資料

- **原始構想文件**：`.ai-dev/issue/減重遊戲功能/sparkle.md`
- **Laravel 文件**：使用 `search-docs` 工具查詢 Laravel 12 相關文件
- **現有程式碼**：參考 `WeightController`、`WeightGoalController` 的實作風格

---

**祝開發順利！** 🎉
