<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MonthlyReport extends Component
{
    public $monthStart = null;
    public $monthEnd = null;
    public $startWeight = null;
    public $endWeight = null;
    public $tasksCompleted = [];
    public $pointsEarned = [];
    public $achievementsUnlocked = [];
    public $longestStreak = 0;
    public $highlights = [];
    public $suggestions = [];

    public function mount(?string $month = null): void
    {
        $this->monthStart = $month 
            ? Carbon::parse($month)->startOfMonth() 
            : Carbon::today()->startOfMonth();
        $this->loadStats();
    }

    public function previousMonth(): void
    {
        $this->monthStart = $this->monthStart->copy()->subMonth();
        $this->loadStats();
    }

    public function nextMonth(): void
    {
        $this->monthStart = $this->monthStart->copy()->addMonth();
        $this->loadStats();
    }

    private function loadStats(): void
    {
        $user = Auth::user();
        $this->monthEnd = $this->monthStart->copy()->endOfMonth();

        $this->startWeight = $this->getMonthStartWeight($user, $this->monthStart);
        $this->endWeight = $this->getMonthEndWeight($user, $this->monthEnd);
        $this->tasksCompleted = $this->getTaskCompletion($user, $this->monthStart, $this->monthEnd);
        $this->pointsEarned = $this->getPointsEarned($user, $this->monthStart, $this->monthEnd);
        $this->achievementsUnlocked = $this->getUnlockedAchievements($user, $this->monthStart, $this->monthEnd);
        $this->longestStreak = $user->longest_streak;
        $this->highlights = $this->getHighlights($user, $this->monthStart, $this->monthEnd);
        $this->suggestions = $this->getSuggestions($user, $this->monthStart, $this->monthEnd);
    }

    private function getMonthStartWeight($user, Carbon $monthStart): ?float
    {
        return $user->dailyLogs()
            ->where('date', '>=', $monthStart)
            ->whereNotNull('weight')
            ->orderBy('date')
            ->first()?->weight;
    }

    private function getMonthEndWeight($user, Carbon $monthEnd): ?float
    {
        return $user->dailyLogs()
            ->where('date', '<=', $monthEnd)
            ->whereNotNull('weight')
            ->orderBy('date', 'desc')
            ->first()?->weight;
    }

    private function getTaskCompletion($user, Carbon $monthStart, Carbon $monthEnd): array
    {
        $logs = $user->dailyLogs()
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->get();

        return [
            'total_days' => $logs->count(),
            'completed_days' => $logs->filter(fn($log) => $log->isAllTasksCompleted())->count(),
            'partial_days' => $logs->filter(fn($log) => ($log->daily_points ?? 0) > 0 && !$log->isAllTasksCompleted())->count(),
            'completion_rate' => $logs->count() > 0 ? round(($logs->filter(fn($log) => $log->isAllTasksCompleted())->count() / $logs->count()) * 100, 1) : 0,
        ];
    }

    private function getPointsEarned($user, Carbon $monthStart, Carbon $monthEnd): array
    {
        $logs = $user->dailyLogs()
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->get();

        return [
            'daily_points' => $logs->sum('daily_points'),
            'weekly_points' => $logs->sum('weekly_points'),
            'total' => $logs->sum('daily_points') + $logs->sum('weekly_points'),
        ];
    }

    private function getUnlockedAchievements($user, Carbon $monthStart, Carbon $monthEnd): array
    {
        return $user->achievements()
            ->wherePivotBetween('unlocked_at', [$monthStart, $monthEnd])
            ->get()
            ->toArray();
    }

    private function getHighlights($user, Carbon $monthStart, Carbon $monthEnd): array
    {
        $highlights = [];
        $logs = $user->dailyLogs()
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->get();

        $completedDays = $logs->filter(fn($log) => $log->isAllTasksCompleted())->count();
        if ($completedDays >= 25) {
            $highlights[] = "本月完成了 {$completedDays} 天的所有任務，表現優異！";
        }

        $walkCount = $logs->filter(fn($log) => $log->task_walk)->count();
        if ($walkCount >= 20) {
            $highlights[] = "本月散步了 {$walkCount} 次，運動習慣良好！";
        }

        $startWeight = $this->getMonthStartWeight($user, $monthStart);
        $endWeight = $this->getMonthEndWeight($user, $monthEnd);
        if ($startWeight && $endWeight && ($startWeight - $endWeight) >= 1) {
            $weightLost = round($startWeight - $endWeight, 1);
            $highlights[] = "本月減重 {$weightLost} 公斤，持續進步中！";
        }

        if (empty($highlights)) {
            $highlights[] = "繼續努力，下個月會更好！";
        }

        return $highlights;
    }

    private function getSuggestions($user, Carbon $monthStart, Carbon $monthEnd): array
    {
        $suggestions = [];
        $logs = $user->dailyLogs()
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->get();

        $completionRate = $logs->count() > 0 
            ? ($logs->filter(fn($log) => $log->isAllTasksCompleted())->count() / $logs->count()) * 100 
            : 0;

        if ($completionRate < 50) {
            $suggestions[] = "任務完成率較低，建議設定小目標，逐步提升完成率";
        }

        if ($user->current_streak < 3) {
            $suggestions[] = "連續達成天數較短，建議每天完成至少 3 個任務，建立習慣";
        }

        $walkCount = $logs->filter(fn($log) => $log->task_walk)->count();
        if ($walkCount < 15) {
            $suggestions[] = "散步次數可以增加，建議每週至少散步 4 次";
        }

        if (empty($suggestions)) {
            $suggestions[] = "表現優異，繼續保持！";
        }

        return $suggestions;
    }

    public function render()
    {
        return view('livewire.monthly-report');
    }
}
