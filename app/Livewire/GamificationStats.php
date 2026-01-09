<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class GamificationStats extends Component
{
    public $days = 30;
    public $pointsData = [];
    public $completionData = [];
    public $streakData = [];

    public function mount(): void
    {
        $this->loadStats();
    }

    public function updatedDays(): void
    {
        $this->loadStats();
        $this->dispatch('stats-updated');
    }

    private function loadStats(): void
    {
        $user = Auth::user();
        
        $this->pointsData = $this->getPointsTrend($user, $this->days);
        $this->completionData = $this->getTaskCompletionRate($user, $this->days);
        $this->streakData = $this->getStreakTrend($user, $this->days);
    }

    /**
     * 計算積分趨勢
     */
    private function getPointsTrend($user, int $days = 30): array
    {
        $startDate = Carbon::today()->subDays($days - 1);

        $logs = $user->dailyLogs()
            ->where('date', '>=', $startDate)
            ->orderBy('date')
            ->get();

        $labels = [];
        $dailyPoints = [];
        $weeklyPoints = [];
        $cumulativePoints = 0;

        foreach ($logs as $log) {
            $labels[] = $log->date->format('m/d');
            $dailyPoints[] = $log->daily_points ?? 0;
            $weeklyPoints[] = $log->weekly_points ?? 0;
            $cumulativePoints += ($log->daily_points ?? 0) + ($log->weekly_points ?? 0);
        }

        return [
            'labels' => $labels,
            'daily_points' => $dailyPoints,
            'weekly_points' => $weeklyPoints,
            'total_points' => $cumulativePoints,
        ];
    }

    /**
     * 計算任務完成率
     */
    private function getTaskCompletionRate($user, int $days = 30): array
    {
        $startDate = Carbon::today()->subDays($days - 1);

        $logs = $user->dailyLogs()
            ->where('date', '>=', $startDate)
            ->get();

        $total = $logs->count();
        $completed = $logs->filter(fn($log) => $log->isAllTasksCompleted())->count();
        $partial = $logs->filter(fn($log) => ($log->daily_points ?? 0) > 0 && !$log->isAllTasksCompleted())->count();
        $none = $total - $completed - $partial;

        return [
            'completed' => $completed,
            'partial' => $partial,
            'none' => $none,
            'total' => $total,
            'rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
        ];
    }

    /**
     * 計算連續達成天數趨勢
     */
    private function getStreakTrend($user, int $days = 30): array
    {
        $startDate = Carbon::today()->subDays($days - 1);

        $logs = $user->dailyLogs()
            ->where('date', '>=', $startDate)
            ->orderBy('date')
            ->get()
            ->keyBy(function ($log) {
                return $log->date->format('Y-m-d');
            });

        $labels = [];
        $streaks = [];
        $currentStreak = 0;

        $date = $startDate->copy();
        while ($date <= Carbon::today()) {
            $dateStr = $date->format('Y-m-d');
            $labels[] = $date->format('m/d');

            if (isset($logs[$dateStr])) {
                $log = $logs[$dateStr];
                if ($log->isAllTasksCompleted()) {
                    $currentStreak++;
                } else {
                    $currentStreak = 0;
                }
            } else {
                $currentStreak = 0;
            }

            $streaks[] = $currentStreak;
            $date->addDay();
        }

        return [
            'labels' => $labels,
            'streaks' => $streaks,
            'max_streak' => count($streaks) > 0 ? max($streaks) : 0,
            'current_streak' => count($streaks) > 0 ? end($streaks) : 0,
        ];
    }

    public function render()
    {
        return view('livewire.gamification-stats');
    }
}
