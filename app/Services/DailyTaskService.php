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
    public function getTodayTasks(?Carbon $date = null): array
    {
        $date = $date ?? Carbon::today();
        $isWeekend = in_array($date->dayOfWeek, [0, 6]); // 0=週日, 6=週六

        if ($isWeekend) {
            return [
                [
                    'key' => 'task_meal',
                    'name' => '只吃 2 餐',
                    'description' => '一天只吃兩餐，可陪家人',
                    'icon' => '🍽️',
                    'points' => 10
                ],
                [
                    'key' => 'task_walk',
                    'name' => '散步 1 次',
                    'description' => '散步至少 30 分鐘',
                    'icon' => '🚶',
                    'points' => 20
                ],
                [
                    'key' => 'task_no_snack',
                    'name' => '不吃宵夜',
                    'description' => '晚上 9 點後不進食',
                    'icon' => '🌙',
                    'points' => 10
                ],
                [
                    'key' => 'task_no_sugar',
                    'name' => '不喝糖飲',
                    'description' => '不喝含糖飲料',
                    'icon' => '🥤',
                    'points' => 10
                ],
            ];
        } else {
            return [
                [
                    'key' => 'task_meal',
                    'name' => '只吃晚餐',
                    'description' => '工作日只吃一餐晚餐',
                    'icon' => '🍽️',
                    'points' => 10
                ],
                [
                    'key' => 'task_walk',
                    'name' => '中午散步',
                    'description' => '中午散步 30 分鐘',
                    'icon' => '🚶',
                    'points' => 20
                ],
                [
                    'key' => 'task_no_snack',
                    'name' => '不吃宵夜',
                    'description' => '晚上 9 點後不進食',
                    'icon' => '🌙',
                    'points' => 10
                ],
                [
                    'key' => 'task_sleep',
                    'name' => '早點睡',
                    'description' => '晚上 11:00 前睡覺',
                    'icon' => '😴',
                    'points' => 10
                ],
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
            if ($dailyLog->task_meal) {
                $points += 10;
            }
            if ($dailyLog->task_walk) {
                $points += 20;
            }
            if ($dailyLog->task_no_snack) {
                $points += 10;
            }
            if ($dailyLog->task_no_sugar) {
                $points += 10;
            }
        } else {
            if ($dailyLog->task_meal) {
                $points += 10;
            }
            if ($dailyLog->task_walk) {
                $points += 20;
            }
            if ($dailyLog->task_no_snack) {
                $points += 10;
            }
            if ($dailyLog->task_sleep) {
                $points += 10;
            }
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
