<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Services\AchievementService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AchievementController extends Controller
{
    public function __construct(
        private AchievementService $achievementService
    ) {
    }

    /**
     * 顯示成就牆
     */
    public function index(): View
    {
        $user = auth()->user();

        // 取得使用者的動態體重里程碑
        $weightMilestones = $user->weight_milestones;

        // 取得已解鎖的體重里程碑階段
        $unlockedStages = $user->achievements()
            ->where('type', 'weight_milestone')
            ->pluck('code')
            ->map(function ($code) {
                return (int) str_replace('milestone_', '', $code);
            })
            ->toArray();

        // 轉換為成就格式
        $weightMilestoneAchievements = collect($weightMilestones)->map(function ($milestone) use ($user, $unlockedStages) {
            $isUnlocked = in_array($milestone['stage'], $unlockedStages);

            // 如果已解鎖，取得解鎖資訊
            $userAchievement = null;
            if ($isUnlocked) {
                $achievement = Achievement::where('code', 'milestone_' . $milestone['stage'])->first();
                if ($achievement) {
                    $userAchievement = $user->achievements()
                        ->where('achievements.id', $achievement->id)
                        ->first();
                }
            }

            return (object)[
                'id' => 'milestone_' . $milestone['stage'],
                'code' => 'milestone_' . $milestone['stage'],
                'name' => $milestone['name'],
                'description' => $milestone['description'],
                'icon' => $milestone['icon'],
                'type' => 'weight_milestone',
                'requirement_value' => $milestone['weight'],
                'points_reward' => 0,
                'sort_order' => $milestone['stage'],
                'is_unlocked' => $isUnlocked,
                'user_achievement' => $userAchievement,
            ];
        });

        // 取得特殊成就（使用快取）
        $allAchievements = $this->achievementService->getCachedAchievements();
        $specialAchievements = $allAchievements
            ->where('type', 'special')
            ->map(function ($achievement) use ($user) {
                $achievement->is_unlocked = $achievement->isUnlockedBy($user);
                return $achievement;
            });

        // 組合所有成就
        $achievementGroups = [
            'weight_milestone' => $weightMilestoneAchievements,
            'special' => $specialAchievements,
        ];

        // 取得已解鎖的成就ID列表
        $unlockedIds = [];
        foreach ($weightMilestoneAchievements as $milestone) {
            if ($milestone->is_unlocked) {
                $unlockedIds[] = $milestone->id;
            }
        }
        $specialUnlockedIds = $user->achievements()
            ->where('type', 'special')
            ->pluck('achievements.id')
            ->toArray();
        $unlockedIds = array_merge($unlockedIds, $specialUnlockedIds);

        return view('achievements.index', [
            'achievements' => collect($weightMilestoneAchievements)->merge($specialAchievements),
            'achievementGroups' => $achievementGroups,
            'unlockedIds' => $unlockedIds,
        ]);
    }

    /**
     * 顯示成就詳情
     */
    public function show(Achievement $achievement): View
    {
        $user = auth()->user();
        $isUnlocked = $achievement->isUnlockedBy($user);

        $userAchievement = null;
        if ($isUnlocked) {
            $userAchievement = $user->achievements()
                ->where('achievements.id', $achievement->id)
                ->first();
        }

        return view('achievements.show', [
            'achievement' => $achievement,
            'isUnlocked' => $isUnlocked,
            'userAchievement' => $userAchievement,
        ]);
    }
}
