<?php

namespace App\Livewire;

use App\Models\Weight;
use App\Models\DailyLog;
use App\Services\AchievementService;
use App\Services\PointsService;
use App\Services\DailyTaskService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class WeightRecordForm extends Component
{
    public $weight = '';
    public $record_at;
    public $note = '';
    
    public $recordingReward = null;
    public $pointsDeducted = null;
    public $deductionReason = null;
    public $pointsToDeduct = null;
    public $pointsDebt = null;
    public $unlockedAchievements = [];
    public $showTaskReminder = false;
    
    protected AchievementService $achievementService;
    protected PointsService $pointsService;
    protected DailyTaskService $taskService;

    public function boot(
        AchievementService $achievementService,
        PointsService $pointsService,
        DailyTaskService $taskService
    ): void {
        $this->achievementService = $achievementService;
        $this->pointsService = $pointsService;
        $this->taskService = $taskService;
    }

    public function mount(): void
    {
        $this->record_at = Carbon::today()->format('Y-m-d');
    }

    protected function rules(): array
    {
        return [
            'weight' => 'required|numeric|min:20|max:300',
            'record_at' => 'required|date|before_or_equal:today',
            'note' => 'nullable|string|max:500',
        ];
    }

    protected function messages(): array
    {
        return [
            'weight.required' => '體重為必填項目',
            'weight.numeric' => '體重必須為數字',
            'weight.min' => '體重不能少於 20 公斤',
            'weight.max' => '體重不能超過 300 公斤',
            'record_at.required' => '記錄日期為必填項目',
            'record_at.date' => '請輸入有效的日期',
            'record_at.before_or_equal' => '記錄日期不能是未來日期',
            'note.max' => '備註不能超過 500 個字元',
        ];
    }

    public function store(): void
    {
        $this->validate();

        $user = Auth::user();
        if (!$user) {
            $this->addError('auth', '請先登入');
            return;
        }

        $recordDate = Carbon::parse($this->record_at);

        // 檢查同一天是否已有記錄
        $existingWeight = Weight::where('user_id', $user->id)
            ->whereDate('record_at', $recordDate->format('Y-m-d'))
            ->first();

        if ($existingWeight) {
            $this->addError('record_at', '該日期已經記錄過體重了，請編輯現有記錄。');
            return;
        }

        $weight = Weight::create([
            'user_id' => $user->id,
            'weight' => $this->weight,
            'record_at' => $this->record_at,
            'note' => !empty($this->note) ? $this->note : null,
        ]);

        // 同步更新 daily_logs 表（如果是今天的記錄）
        if ($recordDate->isToday()) {
            $dailyLog = $user->dailyLogs()->firstOrNew([
                'date' => $recordDate,
            ]);

            $dailyLog->weight = $this->weight;
            if (!empty($this->note)) {
                $dailyLog->notes = $this->note;
            }

            // 計算每日積分
            $dailyLog->daily_points = $this->taskService->calculateDailyPoints($dailyLog);

            // 如果是週日，計算週任務積分
            if ($recordDate->dayOfWeek === 0) {
                $weekStart = $recordDate->copy()->startOfWeek();
                $dailyLog->weekly_points = $this->taskService->calculateWeeklyPoints($user, $weekStart);
            }

            $dailyLog->save();
        }

        // 清除相關快取
        $this->clearUserWeightCache($user->id);
        $user->clearWeightMilestonesCache();

        // 重置通知狀態
        $this->recordingReward = null;
        $this->pointsDeducted = null;
        $this->deductionReason = null;
        $this->pointsToDeduct = null;
        $this->pointsDebt = null;
        $this->unlockedAchievements = [];
        $this->showTaskReminder = false;

        // 步驟 1：檢查未記錄天數並扣除積分
        $lastWeight = $user->weights()
            ->where('record_at', '<', $recordDate->format('Y-m-d'))
            ->latest('record_at')
            ->first();

        if ($lastWeight) {
            $lastDate = Carbon::parse($lastWeight->record_at);
            $daysDiff = $lastDate->diffInDays($recordDate);

            if ($daysDiff > 1) {
                // 有漏記天數
                $missedDays = $daysDiff - 1;
                $this->pointsToDeduct = $missedDays * 10;

                // 安全扣除積分（確保不會低於 0）
                $this->pointsDeducted = $this->pointsService->deductPointsSafely($user, $this->pointsToDeduct);

                if ($this->pointsDeducted > 0) {
                    $this->deductionReason = "漏記 {$missedDays} 天體重";
                }

                // 如果積分不足，計算還欠多少
                if ($this->pointsDeducted < $this->pointsToDeduct) {
                    $this->pointsDebt = $this->pointsToDeduct - $this->pointsDeducted;
                }
            }
        }

        // 步驟 2：給予記錄體重獎勵（只有今天的記錄才給獎勵）
        $isToday = $recordDate->isToday();
        if ($isToday) {
            $this->pointsService->addPoints($user, 20, 'weight_recording');
            $this->recordingReward = 20;
        }

        // 步驟 3：檢查體重里程碑成就
        $weightAchievements = $this->achievementService->checkWeightMilestones($user);

        // 步驟 4：檢查記錄體重成就
        $recordingAchievements = $this->achievementService->checkWeightRecordingAchievements($user);
        $this->unlockedAchievements = array_merge($weightAchievements, $recordingAchievements);

        // 顯示任務提醒
        $this->showTaskReminder = true;

        // 重置表單
        $this->weight = '';
        $this->note = '';
        $this->record_at = Carbon::today()->format('Y-m-d');

        // 觸發事件通知其他組件（如積分顯示）
        $this->dispatch('weight-recorded', [
            'recordingReward' => $this->recordingReward,
            'pointsDeducted' => $this->pointsDeducted,
            'unlockedAchievements' => $this->unlockedAchievements,
        ]);
    }

    private function clearUserWeightCache(int $user_id): void
    {
        // 清除圖表快取
        Cache::forget('chart.weights.user.' . $user_id);

        // 清除最新記錄快取
        Cache::forget('latest.weights.user.' . $user_id);

        // 清除分頁快取
        for ($page = 1; $page <= 10; $page++) {
            Cache::forget('weights.user.' . $user_id . '.page.' . $page . '.filters.' . md5('a:0:{}'));
        }
    }

    public function render()
    {
        return view('livewire.weight-record-form');
    }
}
