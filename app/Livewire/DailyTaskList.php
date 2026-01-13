<?php

namespace App\Livewire;

use App\Models\DailyLog;
use App\Services\AchievementService;
use App\Services\DailyTaskService;
use App\Services\PointsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DailyTaskList extends Component
{
    public $tasks = [];
    public $dailyLog = null;
    public $dailyPoints = 0;
    public $availablePoints = 0;
    public $currentStreak = 0;
    public $longestStreak = 0;
    public $isWeekend = false;
    public $unlockedAchievements = [];
    public $showAchievementNotification = false;

    // 體重記錄表單
    public $weight = '';
    public $notes = '';

    protected DailyTaskService $taskService;
    protected PointsService $pointsService;
    protected AchievementService $achievementService;

    public function boot(
        DailyTaskService $taskService,
        PointsService $pointsService,
        AchievementService $achievementService
    ): void {
        $this->taskService = $taskService;
        $this->pointsService = $pointsService;
        $this->achievementService = $achievementService;
    }

    public function mount(): void
    {
        $this->loadTasks();
    }

    public function toggleTask($taskKey): void
    {
        if (!$this->dailyLog) {
            $this->dispatch('show-error', message: '請先建立昨日記錄');
            return;
        }

        DB::transaction(function () use ($taskKey) {
            $user = Auth::user();

            // 重新載入 dailyLog 以確保最新狀態
            $this->dailyLog->refresh();

            // 切換任務狀態
            $this->dailyLog->{$taskKey} = !$this->dailyLog->{$taskKey};

            // 記錄舊積分
            $oldPoints = $this->dailyPoints;

            // 重新計算積分
            $this->dailyPoints = $this->taskService->calculateDailyPoints($this->dailyLog);
            $this->dailyLog->daily_points = $this->dailyPoints;

            // 如果是週日，重新計算週任務積分
            if ($this->dailyLog->date->dayOfWeek === 0) {
                $weekStart = $this->dailyLog->date->copy()->startOfWeek();
                $weeklyPoints = $this->taskService->calculateWeeklyPoints($user, $weekStart);
                $this->dailyLog->weekly_points = $weeklyPoints;
            }

            $this->dailyLog->save();

            // 更新用戶積分
            $pointsDiff = $this->dailyPoints - $oldPoints;
            if ($pointsDiff != 0) {
                if ($pointsDiff > 0) {
                    $this->pointsService->addPoints($user, $pointsDiff, 'daily_task');
                } else {
                    $this->pointsService->deductPoints($user, abs($pointsDiff));
                }
                $this->availablePoints = $user->fresh()->available_points;
            }

            // 更新任務狀態
            $this->tasks[$taskKey]['completed'] = $this->dailyLog->{$taskKey};

            // 更新連續達成天數
            $this->updateStreak($user);

            // 重新載入用戶以取得最新 streak
            $user->refresh();
            $this->currentStreak = $user->current_streak;
            $this->longestStreak = $user->longest_streak;

            // 檢查成就
            $this->unlockedAchievements = [];
            if ($this->dailyLog->isAllTasksCompleted()) {
                $this->unlockedAchievements = $this->achievementService->checkSpecialAchievements($user);
                if (count($this->unlockedAchievements) > 0) {
                    $this->showAchievementNotification = true;
                    $this->dispatch('achievement-unlocked', achievements: $this->unlockedAchievements);
                }
            }

            // 觸發積分更新事件
            $this->dispatch('points-updated', points: $this->availablePoints);
        });
    }

    public function storeWeightRecord(): void
    {
        $this->validate([
            'weight' => 'nullable|numeric|min:0|max:300',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () {
            $user = Auth::user();
            $yesterday = Carbon::yesterday();

            // 取得或建立昨日記錄
            $this->dailyLog = $user->dailyLogs()->firstOrNew([
                'date' => $yesterday,
            ]);

            // 更新體重和備註
            if (!empty($this->weight)) {
                $this->dailyLog->weight = $this->weight;
            }
            if (!empty($this->notes)) {
                $this->dailyLog->notes = $this->notes;
            }

            // 計算每日積分
            $this->dailyPoints = $this->taskService->calculateDailyPoints($this->dailyLog);
            $this->dailyLog->daily_points = $this->dailyPoints;

            // 如果是週日，計算週任務積分
            if ($yesterday->dayOfWeek === 0) {
                $weekStart = $yesterday->copy()->startOfWeek();
                $weeklyPoints = $this->taskService->calculateWeeklyPoints($user, $weekStart);
                $this->dailyLog->weekly_points = $weeklyPoints;
            }

            $this->dailyLog->save();

            // 更新連續達成天數
            $this->updateStreak($user);

            // 重新載入任務
            $this->loadTasks();

            // 重置表單
            $this->weight = '';
            $this->notes = '';

            // 檢查成就
            if ($this->dailyLog->isAllTasksCompleted()) {
                $this->unlockedAchievements = $this->achievementService->checkSpecialAchievements($user);
                if (count($this->unlockedAchievements) > 0) {
                    $this->showAchievementNotification = true;
                    $this->dispatch('achievement-unlocked', achievements: $this->unlockedAchievements);
                }
            }

            // 如果有體重記錄,檢查體重里程碑
            if ($this->dailyLog->weight) {
                $weightAchievements = $this->achievementService->checkWeightMilestones($user);
                $this->unlockedAchievements = array_merge($this->unlockedAchievements, $weightAchievements);
            }
        });
    }

    private function loadTasks(): void
    {
        $user = Auth::user();
        $yesterday = Carbon::yesterday();
        $this->isWeekend = $yesterday->isWeekend();

        $this->dailyLog = $user->dailyLogs()
            ->where('date', $yesterday)
            ->first();

        $tasksList = $this->taskService->getTodayTasks($yesterday);

        // 轉換任務格式並加入完成狀態
        $this->tasks = [];
        foreach ($tasksList as $task) {
            $this->tasks[$task['key']] = [
                'name' => $task['name'],
                'description' => $task['description'],
                'icon' => $task['icon'],
                'completed' => $this->dailyLog ? (bool) $this->dailyLog->{$task['key']} : false,
            ];
        }

        // 載入已有的體重和備註
        if ($this->dailyLog) {
            $this->weight = $this->dailyLog->weight ?? '';
            $this->notes = $this->dailyLog->notes ?? '';
        }

        $this->dailyPoints = $this->dailyLog->daily_points ?? 0;
        $this->availablePoints = $user->available_points;
        $this->currentStreak = $user->current_streak;
        $this->longestStreak = $user->longest_streak;
    }

    private function updateStreak($user): void
    {
        $streak = 0;
        $date = Carbon::yesterday();

        while (true) {
            $log = $user->dailyLogs()
                ->whereDate('date', $date)
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

        // 重新載入用戶以確保取得最新值
        $user->refresh();
        $this->currentStreak = $user->current_streak;
        $this->longestStreak = $user->longest_streak;
    }

    public function getCompletedCountProperty(): int
    {
        return collect($this->tasks)->filter(fn($task) => $task['completed'] ?? false)->count();
    }

    public function getTotalCountProperty(): int
    {
        return count($this->tasks);
    }

    public function getProgressProperty(): float
    {
        return $this->totalCount > 0 ? ($this->completedCount / $this->totalCount) * 100 : 0;
    }

    public function getAllCompletedProperty(): bool
    {
        return $this->completedCount === $this->totalCount && $this->totalCount > 0;
    }

    public function render()
    {
        return view('livewire.daily-task-list');
    }
}
