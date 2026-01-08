<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GamificationStatsController extends Controller
{
    /**
     * 顯示統計圖表頁面
     */
    public function index(): View
    {
        return view('gamification.stats');
    }

    /**
     * 取得積分趨勢數據
     */
    public function pointsTrend(Request $request): JsonResponse
    {
        $user = auth()->user();
        $days = (int) ($request->get('days', 30));

        $data = $this->getPointsTrend($user, $days);

        return response()->json($data);
    }

    /**
     * 取得任務完成率數據
     */
    public function taskCompletion(Request $request): JsonResponse
    {
        $user = auth()->user();
        $days = (int) ($request->get('days', 30));

        $data = $this->getTaskCompletionRate($user, $days);

        return response()->json($data);
    }

    /**
     * 取得連續達成天數趨勢
     */
    public function streakTrend(Request $request): JsonResponse
    {
        $user = auth()->user();
        $days = (int) ($request->get('days', 30));

        $data = $this->getStreakTrend($user, $days);

        return response()->json($data);
    }

    /**
     * 計算積分趨勢
     */
    private function getPointsTrend(User $user, int $days = 30): array
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
    private function getTaskCompletionRate(User $user, int $days = 30): array
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
    private function getStreakTrend(User $user, int $days = 30): array
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
            'max_streak' => max($streaks),
            'current_streak' => end($streaks),
        ];
    }
}
