<?php

namespace Database\Seeders;

use App\Models\Achievement;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 體重里程碑成就
        $weightMilestones = [
            ['code' => 'weight_107', 'name' => '起步者', 'icon' => '🎖️', 'requirement_value' => 107.0, 'points_reward' => 0, 'sort_order' => 1],
            ['code' => 'weight_105', 'name' => '認真了', 'icon' => '🎖️', 'requirement_value' => 105.0, 'points_reward' => 0, 'sort_order' => 2],
            ['code' => 'weight_100', 'name' => '破百', 'icon' => '🏅', 'requirement_value' => 100.0, 'points_reward' => 0, 'sort_order' => 3],
            ['code' => 'weight_95', 'name' => '過半', 'icon' => '🏅', 'requirement_value' => 95.0, 'points_reward' => 0, 'sort_order' => 4],
            ['code' => 'weight_90', 'name' => 'BMI 降級', 'icon' => '🏆', 'requirement_value' => 90.0, 'points_reward' => 0, 'sort_order' => 5],
            ['code' => 'weight_85', 'name' => '接近目標', 'icon' => '🏆', 'requirement_value' => 85.0, 'points_reward' => 0, 'sort_order' => 6],
            ['code' => 'weight_80', 'name' => '終極勝利', 'icon' => '👑', 'requirement_value' => 80.0, 'points_reward' => 0, 'sort_order' => 7],
        ];

        foreach ($weightMilestones as $milestone) {
            Achievement::create([
                'code' => $milestone['code'],
                'name' => $milestone['name'],
                'description' => $this->getWeightMilestoneDescription($milestone['requirement_value']),
                'icon' => $milestone['icon'],
                'type' => 'weight_milestone',
                'requirement_value' => $milestone['requirement_value'],
                'points_reward' => $milestone['points_reward'],
                'sort_order' => $milestone['sort_order'],
            ]);
        }

        // 特殊成就
        $specialAchievements = [
            ['code' => 'perfect_week', 'name' => '完美一週', 'icon' => '⭐', 'description' => '連續 7 天完成所有每日任務', 'points_reward' => 100],
            ['code' => 'perfect_month', 'name' => '完美一月', 'icon' => '🌟', 'description' => '連續 30 天完成所有每日任務', 'points_reward' => 500],
            ['code' => 'weekend_warrior', 'name' => '週末戰士', 'icon' => '💪', 'description' => '連續 4 個週末都完成任務', 'points_reward' => 200],
            ['code' => 'money_saver', 'name' => '省錢達人', 'icon' => '💰', 'description' => '累積省下 NT$50,000', 'points_reward' => 300],
            ['code' => 'walk_master', 'name' => '散步狂人', 'icon' => '🚶', 'description' => '累積散步 100 次', 'points_reward' => 200],
            ['code' => 'early_bird', 'name' => '早睡冠軍', 'icon' => '😴', 'description' => '連續 30 天 11:00 前睡覺', 'points_reward' => 200],
            ['code' => 'fasting_master', 'name' => '斷食大師', 'icon' => '🍽️', 'description' => '連續 30 天只吃 1 餐', 'points_reward' => 300],
        ];

        foreach ($specialAchievements as $achievement) {
            Achievement::create([
                'code' => $achievement['code'],
                'name' => $achievement['name'],
                'description' => $achievement['description'],
                'icon' => $achievement['icon'],
                'type' => 'special',
                'requirement_value' => null,
                'points_reward' => $achievement['points_reward'],
                'sort_order' => 100, // 特殊成就排在後面
            ]);
        }
    }

    private function getWeightMilestoneDescription(float $weight): string
    {
        $descriptions = [
            107.0 => '萬事起頭難，你已經邁出第一步！',
            105.0 => '連續達成目標，證明你是認真的！',
            100.0 => '重大里程碑！體重回到兩位數！',
            95.0 => '已經完成一半的旅程！',
            90.0 => 'BMI 從肥胖降級到過重，健康大躍進！',
            85.0 => '勝利在望！再堅持一下！',
            80.0 => '恭喜！你靠自己的意志力達成目標！',
        ];

        return $descriptions[$weight] ?? '';
    }
}
