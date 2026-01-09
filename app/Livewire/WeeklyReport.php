<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class WeeklyReport extends Component
{
    public $weekStart = null;
    public $weekEnd = null;
    public $startWeight = null;
    public $endWeight = null;
    public $tasksCompleted = [];
    public $pointsEarned = [];
    public $achievementsUnlocked = [];
    public $streak = 0;
    public $nextWeekGoal = '';

    public function mount(?string $weekStart = null): void
    {
        $this->weekStart = $weekStart 
            ? Carbon::parse($weekStart)->startOfWeek() 
            : Carbon::today()->startOfWeek();
        $this->loadStats();
    }

    public function previousWeek(): void
    {
        $this->weekStart = $this->weekStart->copy()->subWeek();
        $this->loadStats();
    }

    public function nextWeek(): void
    {
        $this->weekStart = $this->weekStart->copy()->addWeek();
        $this->loadStats();
    }

    private function loadStats(): void
    {
        $user = Auth::user();
        $this->weekEnd = $this->weekStart->copy()->endOfWeek();

        $this->startWeight = $this->getWeekStartWeight($user, $this->weekStart);
        $this->endWeight = $this->getWeekEndWeight($user, $this->weekEnd);
        $this->tasksCompleted = $this->getTaskCompletion($user, $this->weekStart, $this->weekEnd);
        $this->pointsEarned = $this->getPointsEarned($user, $this->weekStart, $this->weekEnd);
        $this->achievementsUnlocked = $this->getUnlockedAchievements($user, $this->weekStart, $this->weekEnd);
        $this->streak = $user->current_streak;
        $this->nextWeekGoal = $this->getNextWeekGoal($user, $this->weekEnd);
    }

    private function getWeekStartWeight($user, Carbon $weekStart): ?float
    {
        return $user->dailyLogs()
            ->where('date', '>=', $weekStart)
            ->whereNotNull('weight')
            ->orderBy('date')
            ->first()?->weight;
    }

    private function getWeekEndWeight($user, Carbon $weekEnd): ?float
    {
        return $user->dailyLogs()
            ->where('date', '<=', $weekEnd)
            ->whereNotNull('weight')
            ->orderBy('date', 'desc')
            ->first()?->weight;
    }

    private function getTaskCompletion($user, Carbon $weekStart, Carbon $weekEnd): array
    {
        $logs = $user->dailyLogs()
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->get();

        $workdayLogs = $logs->filter(fn($log) => !in_array($log->date->dayOfWeek, [0, 6]));
        $weekendLogs = $logs->filter(fn($log) => in_array($log->date->dayOfWeek, [0, 6]));

        return [
            'total_days' => $logs->count(),
            'completed_days' => $logs->filter(fn($log) => $log->isAllTasksCompleted())->count(),
            'workday_completed' => $workdayLogs->filter(fn($log) => $log->isAllTasksCompleted())->count(),
            'workday_total' => $workdayLogs->count(),
            'weekend_completed' => $weekendLogs->filter(fn($log) => $log->isAllTasksCompleted())->count(),
            'weekend_total' => $weekendLogs->count(),
        ];
    }

    private function getPointsEarned($user, Carbon $weekStart, Carbon $weekEnd): array
    {
        $logs = $user->dailyLogs()
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->get();

        return [
            'daily_points' => $logs->sum('daily_points'),
            'weekly_points' => $logs->sum('weekly_points'),
            'total' => $logs->sum('daily_points') + $logs->sum('weekly_points'),
        ];
    }

    private function getUnlockedAchievements($user, Carbon $weekStart, Carbon $weekEnd): array
    {
        return $user->achievements()
            ->wherePivotBetween('unlocked_at', [$weekStart, $weekEnd])
            ->get()
            ->toArray();
    }

    private function getNextWeekGoal($user, Carbon $weekEnd): string
    {
        $currentStreak = $user->current_streak;
        $targetWeight = $user->activeWeightGoal?->target_weight;

        if ($currentStreak >= 7) {
            return '繼續保持完美記錄！';
        } elseif ($currentStreak >= 3) {
            return '再堅持 ' . (7 - $currentStreak) . ' 天就能達成完美一週！';
        } else {
            return '設定小目標：連續完成 3 天任務';
        }
    }

    public function render()
    {
        return view('livewire.weekly-report');
    }
}
