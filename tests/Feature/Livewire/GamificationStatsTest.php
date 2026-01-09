<?php

namespace Tests\Feature\Livewire;

use App\Livewire\GamificationStats;
use App\Models\User;
use App\Models\DailyLog;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GamificationStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_can_be_rendered(): void
    {
        $user = User::factory()->create();
        assert($user instanceof User);

        Livewire::actingAs($user)
            ->test(GamificationStats::class)
            ->assertSuccessful();
    }

    public function test_displays_default_period(): void
    {
        $user = User::factory()->create();
        assert($user instanceof User);

        Livewire::actingAs($user)
            ->test(GamificationStats::class)
            ->assertSet('days', 30);
    }

    public function test_changes_time_period(): void
    {
        $user = User::factory()->create();
        assert($user instanceof User);

        Livewire::actingAs($user)
            ->test(GamificationStats::class)
            ->set('days', 7)
            ->assertSet('days', 7)
            ->assertDispatched('stats-updated');
    }

    public function test_calculates_points_trend(): void
    {
        $user = User::factory()->create();
        assert($user instanceof User);

        // 創建一些記錄
        DailyLog::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::today()->subDays(5),
            'daily_points' => 50,
            'weekly_points' => 0,
        ]);

        DailyLog::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::today()->subDays(2),
            'daily_points' => 30,
            'weekly_points' => 100,
        ]);

        Livewire::actingAs($user)
            ->test(GamificationStats::class)
            ->set('days', 7)
            ->assertSet('pointsData.total_points', 180); // 50 + 30 + 100
    }

    public function test_calculates_task_completion_rate(): void
    {
        $user = User::factory()->create();
        assert($user instanceof User);

        // 創建完成的記錄
        DailyLog::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::today()->subDays(2),
            'task_meal' => true,
            'task_walk' => true,
            'task_no_snack' => true,
            'task_sleep' => true,
            'daily_points' => 50,
        ]);

        // 創建部分完成的記錄
        DailyLog::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::today()->subDays(1),
            'task_meal' => true,
            'task_walk' => false,
            'task_no_snack' => true,
            'task_sleep' => false,
            'daily_points' => 20,
        ]);

        Livewire::actingAs($user)
            ->test(GamificationStats::class)
            ->set('days', 7)
            ->assertSet('completionData.completed', 1)
            ->assertSet('completionData.partial', 1);
    }

    public function test_calculates_streak_trend(): void
    {
        $user = User::factory()->create();
        assert($user instanceof User);

        // 創建連續完成的記錄（包括今天）
        DailyLog::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::today()->subDays(1),
            'task_meal' => true,
            'task_walk' => true,
            'task_no_snack' => true,
            'task_sleep' => true,
        ]);

        DailyLog::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'task_meal' => true,
            'task_walk' => true,
            'task_no_snack' => true,
            'task_sleep' => true,
        ]);

        Livewire::actingAs($user)
            ->test(GamificationStats::class)
            ->set('days', 7);

        // 檢查 streak 數據結構
        $streakData = Livewire::actingAs($user)
            ->test(GamificationStats::class)
            ->get('streakData');
        
        $this->assertIsArray($streakData);
        $this->assertArrayHasKey('current_streak', $streakData);
        $this->assertGreaterThanOrEqual(1, $streakData['current_streak']);
    }

    public function test_handles_empty_data(): void
    {
        $user = User::factory()->create();
        assert($user instanceof User);

        Livewire::actingAs($user)
            ->test(GamificationStats::class)
            ->assertSet('pointsData.labels', [])
            ->assertSet('completionData.total', 0)
            ->assertSet('streakData.max_streak', 0);
    }
}
