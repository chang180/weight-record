<?php

namespace App\Services;

use App\Models\User;
use App\Models\Achievement;
use App\Models\DailyLog;
use App\Models\Weight;
use App\Services\PointsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Collection;

class AchievementService
{
    public function __construct(
        private PointsService $pointsService
    ) {
    }

    /**
     * 取得快取的成就定義資料
     */
    public function getCachedAchievements(): Collection
    {
        return Cache::remember('achievements.all', 3600, function () {
            return Achievement::orderBy('sort_order')->get();
        });
    }

    /**
     * 檢查體重里程碑成就（動態生成）
     */
    public function checkWeightMilestones(User $user): array
    {
        $unlocked = [];
        $currentWeight = $user->current_weight;

        if (!$currentWeight || !$user->start_weight) {
            return $unlocked;
        }

        // 取得使用者的個人化里程碑
        $milestones = $user->weight_milestones;

        if (empty($milestones)) {
            return $unlocked;
        }

        // 檢查已解鎖的里程碑階段
        $unlockedStages = $user->achievements()
            ->where('type', 'weight_milestone')
            ->pluck('code')
            ->map(function ($code) {
                // 從 code 中提取階段數字，例如 "milestone_1" -> 1
                return (int) str_replace('milestone_', '', $code);
            })
            ->toArray();

        // 檢查每個里程碑是否應該解鎖
        foreach ($milestones as $milestone) {
            $stage = $milestone['stage'];
            $targetWeight = $milestone['weight'];

            // 如果當前體重已達到或低於此里程碑，且尚未解鎖
            if ($currentWeight <= $targetWeight && !in_array($stage, $unlockedStages)) {
                // 動態建立成就記錄
                $achievement = $this->createDynamicMilestone($user, $milestone);
                if ($achievement) {
                    $unlocked[] = $achievement;
                }
            }
        }

        return $unlocked;
    }

    /**
     * 動態建立里程碑成就並解鎖
     */
    private function createDynamicMilestone(User $user, array $milestone): ?Achievement
    {
        $code = 'milestone_' . $milestone['stage'];

        // 檢查是否已存在
        $achievement = Achievement::where('code', $code)
            ->where('type', 'weight_milestone')
            ->first();

        // 如果不存在，建立新的
        if (!$achievement) {
            $achievement = Achievement::create([
                'code' => $code,
                'name' => $milestone['name'],
                'description' => $milestone['description'],
                'icon' => $milestone['icon'],
                'type' => 'weight_milestone',
                'requirement_value' => $milestone['weight'],
                'points_reward' => 0,
                'sort_order' => $milestone['stage'],
            ]);
        }

        // 解鎖成就
        if (!$achievement->isUnlockedBy($user)) {
            $this->unlockAchievement($user, $achievement, $user->current_weight);
            return $achievement;
        }

        return null;
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

            // 清除相關快取
            Cache::forget("user.{$user->id}.unlocked_achievements");
            Cache::forget("user.{$user->id}.weight_milestones");
        });
    }

    /**
     * 取得快取的已解鎖成就
     */
    public function getCachedUnlockedAchievements(User $user): Collection
    {
        $cacheKey = "user.{$user->id}.unlocked_achievements";

        return Cache::remember($cacheKey, 600, function () use ($user) {
            return $user->achievements()->get();
        });
    }

    /**
     * 取得快取的任務完成統計
     */
    private function getCachedCompletionStats(User $user, Carbon $startDate, Carbon $endDate): array
    {
        $cacheKey = "user.{$user->id}.completion_stats.{$startDate->format('Ymd')}.{$endDate->format('Ymd')}";

        return Cache::remember($cacheKey, 300, function () use ($user, $startDate, $endDate) {
            $logs = $user->dailyLogs()
                ->whereBetween('date', [$startDate, $endDate])
                ->get();

            return [
                'total_days' => $logs->count(),
                'completed_days' => $logs->filter(fn($log) => $log->isAllTasksCompleted())->count(),
            ];
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
            ->get()
            ->filter(function ($log) {
                // 只計算工作日（週一到週五）
                // Carbon dayOfWeek: 0=週日, 1=週一, ..., 6=週六
                $dayOfWeek = $log->date->dayOfWeek;
                return $dayOfWeek >= 1 && $dayOfWeek <= 5;
            })
            ->count();

        if ($logs >= 30) {
            $this->unlockAchievement($user, $achievement);
            $unlocked[] = $achievement;
        }
    }

    /**
     * 檢查記錄體重成就
     */
    public function checkWeightRecordingAchievements(User $user): array
    {
        $unlocked = [];

        // 取得連續記錄天數
        $streak = $this->checkWeightRecordingStreak($user);

        // 檢查「記錄習慣養成」（連續記錄 7 天）
        $this->checkDailyRecorder7($user, $streak, $unlocked);

        // 檢查「忠實記錄者」（連續記錄 30 天）
        $this->checkDailyRecorder30($user, $streak, $unlocked);

        return $unlocked;
    }

    /**
     * 計算連續記錄體重的天數
     */
    private function checkWeightRecordingStreak(User $user): int
    {
        // 取得所有體重記錄的日期（唯一日期），按日期降冪排序
        $recordDates = $user->weights()
            ->selectRaw('DATE(record_at) as date')
            ->distinct()
            ->orderBy('date', 'DESC')
            ->pluck('date')
            ->map(function ($date) {
                return Carbon::parse($date)->startOfDay();
            })
            ->values()
            ->all();

        if (empty($recordDates)) {
            return 0;
        }

        // 從最新的記錄日期開始往前回溯，檢查連續記錄天數
        $streak = 0;
        $expectedDate = $recordDates[0]->copy(); // 從最新記錄日期開始
        $dateIndex = 0;

        while ($dateIndex < count($recordDates)) {
            $recordDate = $recordDates[$dateIndex];

            // 如果記錄日期與預期日期相同
            if ($recordDate->equalTo($expectedDate)) {
                $streak++;
                $expectedDate->subDay(); // 預期下一天
                $dateIndex++;
            } else {
                // 如果記錄日期與預期日期不同，表示有間斷，停止計算
                break;
            }
        }

        return $streak;
    }

    /**
     * 檢查記錄習慣養成成就（連續記錄 7 天）
     */
    private function checkDailyRecorder7(User $user, int $streak, array &$unlocked): void
    {
        $achievement = Achievement::where('code', 'daily_recorder_7')->first();
        if (!$achievement || $achievement->isUnlockedBy($user)) {
            return;
        }

        if ($streak >= 7) {
            $this->unlockAchievement($user, $achievement);
            $unlocked[] = $achievement;
        }
    }

    /**
     * 檢查忠實記錄者成就（連續記錄 30 天）
     */
    private function checkDailyRecorder30(User $user, int $streak, array &$unlocked): void
    {
        $achievement = Achievement::where('code', 'daily_recorder_30')->first();
        if (!$achievement || $achievement->isUnlockedBy($user)) {
            return;
        }

        if ($streak >= 30) {
            $this->unlockAchievement($user, $achievement);
            $unlocked[] = $achievement;
        }
    }
}
