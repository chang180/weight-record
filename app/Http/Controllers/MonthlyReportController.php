<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MonthlyReportController extends Controller
{
    /**
     * 顯示月報表
     */
    public function show(Request $request, ?string $month = null): View
    {
        $user = auth()->user();
        $monthStart = $month ? Carbon::parse($month)->startOfMonth() : Carbon::today()->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $stats = [
            'month_start' => $monthStart,
            'month_end' => $monthEnd,
            'start_weight' => $this->getMonthStartWeight($user, $monthStart),
            'end_weight' => $this->getMonthEndWeight($user, $monthEnd),
            'tasks_completed' => $this->getTaskCompletion($user, $monthStart, $monthEnd),
            'points_earned' => $this->getPointsEarned($user, $monthStart, $monthEnd),
            'achievements_unlocked' => $this->getUnlockedAchievements($user, $monthStart, $monthEnd),
            'longest_streak' => $user->longest_streak,
            'highlights' => $this->getHighlights($user, $monthStart, $monthEnd),
            'suggestions' => $this->getSuggestions($user, $monthStart, $monthEnd),
        ];

        return view('reports.monthly', $stats);
    }

    /**
     * 取得月開始體重
     */
    private function getMonthStartWeight(User $user, Carbon $monthStart): ?float
    {
        return $user->dailyLogs()
            ->where('date', '>=', $monthStart)
            ->whereNotNull('weight')
            ->orderBy('date')
            ->first()?->weight;
    }

    /**
     * 取得月結束體重
     */
    private function getMonthEndWeight(User $user, Carbon $monthEnd): ?float
    {
        return $user->dailyLogs()
            ->where('date', '<=', $monthEnd)
            ->whereNotNull('weight')
            ->orderBy('date', 'desc')
            ->first()?->weight;
    }

    /**
     * 取得任務完成統計
     */
    private function getTaskCompletion(User $user, Carbon $monthStart, Carbon $monthEnd): array
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

    /**
     * 取得獲得積分
     */
    private function getPointsEarned(User $user, Carbon $monthStart, Carbon $monthEnd): array
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

    /**
     * 取得解鎖成就
     */
    private function getUnlockedAchievements(User $user, Carbon $monthStart, Carbon $monthEnd): array
    {
        return $user->achievements()
            ->wherePivotBetween('unlocked_at', [$monthStart, $monthEnd])
            ->get()
            ->toArray();
    }

    /**
     * 取得本月亮點
     */
    private function getHighlights(User $user, Carbon $monthStart, Carbon $monthEnd): array
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

    /**
     * 取得建議
     */
    private function getSuggestions(User $user, Carbon $monthStart, Carbon $monthEnd): array
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
}
