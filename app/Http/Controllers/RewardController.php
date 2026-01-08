<?php

namespace App\Http\Controllers;

use App\Models\Reward;
use App\Services\PointsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class RewardController extends Controller
{
    public function __construct(
        private PointsService $pointsService
    ) {
    }

    /**
     * 顯示獎勵商店
     */
    public function index(): View
    {
        $user = auth()->user();

        $rewards = [
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

        return view('rewards.index', [
            'rewards' => $rewards,
            'availablePoints' => $user->available_points,
        ]);
    }

    /**
     * 兌換獎勵
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'reward_type' => 'required|string',
            'reward_name' => 'required|string',
            'points_spent' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        return DB::transaction(function () use ($validated) {
            $user = auth()->user();

            // 檢查積分是否足夠
            if ($user->available_points < $validated['points_spent']) {
                return back()->withErrors(['points' => '積分不足']);
            }

            // 扣除積分
            if (!$this->pointsService->deductPoints($user, $validated['points_spent'])) {
                return back()->withErrors(['points' => '積分扣除失敗']);
            }

            // 記錄兌換
            $reward = Reward::create([
                'user_id' => $user->id,
                'reward_type' => $validated['reward_type'],
                'reward_name' => $validated['reward_name'],
                'points_spent' => $validated['points_spent'],
                'redeemed_at' => now(),
                'notes' => $validated['notes'] ?? null,
            ]);

            return redirect()->route('rewards.history')
                ->with('success', '獎勵兌換成功！');
        });
    }

    /**
     * 顯示兌換歷史
     */
    public function history(): View
    {
        $user = auth()->user();

        $rewards = $user->rewards()
            ->orderBy('redeemed_at', 'desc')
            ->paginate(20);

        return view('rewards.history', [
            'rewards' => $rewards,
        ]);
    }
}
