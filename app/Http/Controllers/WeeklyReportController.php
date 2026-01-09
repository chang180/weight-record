<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WeeklyReportController extends Controller
{
    /**
     * 顯示週報表
     */
    public function show(Request $request, ?string $weekStart = null): View
    {
        $user = auth()->user();
        $weekStart = $weekStart ? Carbon::parse($weekStart)->startOfWeek() : Carbon::today()->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();

        $stats = [
            'week_start' => $weekStart,
            'week_end' => $weekEnd,
            'start_weight' => $this->getWeekStartWeight($user, $weekStart),
            'end_weight' => $this->getWeekEndWeight($user, $weekEnd),
            'tasks_completed' => $this->getTaskCompletion($user, $weekStart, $weekEnd),
            'points_earned' => $this->getPointsEarned($user, $weekStart, $weekEnd),
            'achievements_unlocked' => $this->getUnlockedAchievements($user, $weekStart, $weekEnd),
            'streak' => $user->current_streak,
            'next_week_goal' => $this->getNextWeekGoal($user, $weekEnd),
        ];

        return view('reports.weekly', array_merge($stats, ['weekStart' => $weekStart->format('Y-m-d')]));
    }

    /**
     * 取得週開始體重
     */
    private function getWeekStartWeight(User $user, Carbon $weekStart): ?float
    {
        return $user->dailyLogs()
            ->where('date', '>=', $weekStart)
            ->whereNotNull('weight')
            ->orderBy('date')
            ->first()?->weight;
    }

    /**
     * 取得週結束體重
     */
    private function getWeekEndWeight(User $user, Carbon $weekEnd): ?float
    {
        return $user->dailyLogs()
            ->where('date', '<=', $weekEnd)
            ->whereNotNull('weight')
            ->orderBy('date', 'desc')
            ->first()?->weight;
    }

    /**
     * 取得任務完成統計
     */
    private function getTaskCompletion(User $user, Carbon $weekStart, Carbon $weekEnd): array
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

    /**
     * 取得獲得積分
     */
    private function getPointsEarned(User $user, Carbon $weekStart, Carbon $weekEnd): array
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

    /**
     * 取得解鎖成就
     */
    private function getUnlockedAchievements(User $user, Carbon $weekStart, Carbon $weekEnd): array
    {
        return $user->achievements()
            ->wherePivotBetween('unlocked_at', [$weekStart, $weekEnd])
            ->get()
            ->toArray();
    }

    /**
     * 取得下週目標建議
     */
    private function getNextWeekGoal(User $user, Carbon $weekEnd): string
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
}
