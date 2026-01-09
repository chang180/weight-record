<?php

namespace App\Http\Controllers;

use App\Models\DailyLog;
use App\Services\DailyTaskService;
use App\Services\PointsService;
use App\Services\AchievementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\View\View;

class DailyLogController extends Controller
{
    public function __construct(
        private DailyTaskService $taskService,
        private PointsService $pointsService,
        private AchievementService $achievementService
    ) {
    }

    /**
     * 顯示今日任務儀表板
     */
    public function index(): View
    {
        $user = auth()->user();
        $today = Carbon::today();
        $isWeekend = $today->isWeekend();

        $dailyLog = $user->dailyLogs()
            ->where('date', $today)
            ->first();

        $tasksList = $this->taskService->getTodayTasks($today);

        // 轉換任務格式並加入完成狀態
        $tasks = [];
        foreach ($tasksList as $task) {
            $tasks[$task['key']] = [
                'name' => $task['name'],
                'description' => $task['description'],
                'icon' => $task['icon'],
                'completed' => $dailyLog ? (bool) $dailyLog->{$task['key']} : false,
            ];
        }

        return view('daily-log.index', [
            'dailyLog' => $dailyLog,
            'tasks' => $tasks,
            'isWeekend' => $isWeekend,
            'today' => $today,
        ]);
    }

    /**
     * 建立或更新每日記錄
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'weight' => 'nullable|numeric|min:0|max:300',
            'notes' => 'nullable|string|max:1000',
        ]);

        return DB::transaction(function () use ($validated) {
            $user = auth()->user();
            $today = Carbon::today();

            // 取得或建立今日記錄
            $dailyLog = $user->dailyLogs()->firstOrNew([
                'date' => $today,
            ]);

            // 更新體重和備註
            if (isset($validated['weight'])) {
                $dailyLog->weight = $validated['weight'];
            }
            if (isset($validated['notes'])) {
                $dailyLog->notes = !empty($validated['notes']) ? $validated['notes'] : null;
            }

            // 計算每日積分
            $dailyPoints = $this->taskService->calculateDailyPoints($dailyLog);
            $dailyLog->daily_points = $dailyPoints;

            // 如果是週日，計算週任務積分
            if ($today->dayOfWeek === 0) {
                $weekStart = $today->copy()->startOfWeek();
                $weeklyPoints = $this->taskService->calculateWeeklyPoints($user, $weekStart);
                $dailyLog->weekly_points = $weeklyPoints;
            }

            $dailyLog->save();

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

            $achievementText = count($unlockedAchievements) > 0
                ? $unlockedAchievements[0]->name
                : null;

            return redirect()->route('daily-logs.index')
                ->with('success', '記錄已更新')
                ->with('achievement', $achievementText);
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

        return DB::transaction(function () use ($dailyLog, $validated) {
            $user = auth()->user();
            $taskKey = $validated['task'];

            // 切換任務狀態
            $dailyLog->{$taskKey} = !$dailyLog->{$taskKey};

            // 記錄舊積分
            $oldPoints = $dailyLog->daily_points ?? 0;

            // 重新計算積分
            $dailyPoints = $this->taskService->calculateDailyPoints($dailyLog);
            $dailyLog->daily_points = $dailyPoints;

            // 如果是週日，重新計算週任務積分
            if ($dailyLog->date->dayOfWeek === 0) {
                $weekStart = $dailyLog->date->copy()->startOfWeek();
                $weeklyPoints = $this->taskService->calculateWeeklyPoints($user, $weekStart);
                $dailyLog->weekly_points = $weeklyPoints;
            }

            $dailyLog->save();

            // 更新用戶積分
            $pointsDiff = $dailyPoints - $oldPoints;
            if ($pointsDiff != 0) {
                if ($pointsDiff > 0) {
                    $this->pointsService->addPoints($user, $pointsDiff, 'daily_task');
                } else {
                    $this->pointsService->deductPoints($user, abs($pointsDiff));
                }
            }

            // 更新連續達成天數
            $this->updateStreak($user);

            // 檢查成就
            $unlockedAchievements = [];
            if ($dailyLog->isAllTasksCompleted()) {
                $unlockedAchievements = $this->achievementService->checkSpecialAchievements($user);
            }

            return response()->json([
                'success' => true,
                'dailyLog' => [
                    'task_meal' => $dailyLog->task_meal,
                    'task_walk' => $dailyLog->task_walk,
                    'task_no_snack' => $dailyLog->task_no_snack,
                    'task_sleep' => $dailyLog->task_sleep,
                    'task_no_sugar' => $dailyLog->task_no_sugar,
                ],
                'dailyPoints' => $dailyPoints,
                'unlockedAchievements' => array_map(fn($a) => ['name' => $a->name, 'icon' => $a->icon], $unlockedAchievements),
            ]);
        });
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
