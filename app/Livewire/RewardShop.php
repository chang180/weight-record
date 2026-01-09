<?php

namespace App\Livewire;

use App\Models\Reward;
use App\Services\PointsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class RewardShop extends Component
{
    public $rewards = [];
    public $availablePoints = 0;
    public $selectedReward = null;
    public $showRedeemModal = false;
    public $notes = '';

    protected PointsService $pointsService;

    public function boot(PointsService $pointsService): void
    {
        $this->pointsService = $pointsService;
    }

    public function mount(): void
    {
        $this->loadRewards();
    }

    private function loadRewards(): void
    {
        $user = Auth::user();
        $this->availablePoints = $user->available_points;

        $this->rewards = [
            [
                'type' => 'indulgence_meal',
                'name' => '放縱餐券',
                'points' => 500,
                'description' => '週末可以多吃一餐「放縱餐」，不扣分',
            ],
            [
                'type' => 'small_reward',
                'name' => '小確幸',
                'points' => 1000,
                'description' => '買一件想要的東西（NT$500 以內）',
            ],
            [
                'type' => 'family_time',
                'name' => '親子時光',
                'points' => 2000,
                'description' => '帶家人去吃好料',
            ],
            [
                'type' => 'self_reward',
                'name' => '犒賞自己',
                'points' => 3000,
                'description' => '買一個想要的東西（NT$1,000 以內）',
            ],
            [
                'type' => 'big_reward',
                'name' => '大獎勵',
                'points' => 5000,
                'description' => '買一個想要很久的東西（NT$2,000 以內）',
            ],
        ];
    }

    public function selectReward($index): void
    {
        $this->selectedReward = $this->rewards[$index];
        $this->showRedeemModal = true;
        $this->notes = '';
    }

    public function closeModal(): void
    {
        $this->showRedeemModal = false;
        $this->selectedReward = null;
        $this->notes = '';
    }

    public function redeem(): void
    {
        if (!$this->selectedReward) {
            return;
        }

        $this->validate([
            'notes' => 'nullable|string|max:500',
        ], [
            'notes.max' => '備註不能超過 500 個字元',
        ]);

        DB::transaction(function () {
            $user = Auth::user();

            // 檢查積分是否足夠
            if ($user->available_points < $this->selectedReward['points']) {
                $this->addError('points', '積分不足');
                return;
            }

            // 扣除積分
            if (!$this->pointsService->deductPoints($user, $this->selectedReward['points'])) {
                $this->addError('points', '積分扣除失敗');
                return;
            }

            // 記錄兌換
            Reward::create([
                'user_id' => $user->id,
                'reward_type' => $this->selectedReward['type'],
                'reward_name' => $this->selectedReward['name'],
                'points_spent' => $this->selectedReward['points'],
                'redeemed_at' => now(),
                'notes' => !empty($this->notes) ? $this->notes : null,
            ]);

            // 更新可用積分
            $this->availablePoints = $user->fresh()->available_points;

            // 保存獎勵名稱（在關閉模態框之前）
            $rewardName = $this->selectedReward['name'];

            // 關閉模態框並重置
            $this->closeModal();

            // 觸發積分更新事件
            $this->dispatch('points-updated', points: $this->availablePoints);

            // 顯示成功訊息
            $this->dispatch('reward-redeemed', reward: $rewardName);
        });
    }

    public function render()
    {
        return view('livewire.reward-shop');
    }
}
