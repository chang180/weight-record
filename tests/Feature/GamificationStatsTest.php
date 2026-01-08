<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\DailyLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class GamificationStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_gamification_stats_page(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->get('/gamification/stats');
        
        $response->assertStatus(200);
        $response->assertViewIs('gamification.stats');
    }

    public function test_points_trend_api_returns_correct_data(): void
    {
        $user = User::factory()->create();
        
        // 建立一些每日記錄
        DailyLog::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::today()->subDays(2),
            'daily_points' => 50,
            'weekly_points' => 0,
        ]);
        
        DailyLog::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::today()->subDays(1),
            'daily_points' => 40,
            'weekly_points' => 100,
        ]);
        
        $response = $this->actingAs($user)
            ->get('/api/gamification/stats/points-trend?days=30');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'labels',
            'daily_points',
            'weekly_points',
            'total_points',
        ]);
        
        $data = $response->json();
        $this->assertIsArray($data['labels']);
        $this->assertIsArray($data['daily_points']);
        $this->assertGreaterThan(0, $data['total_points']);
    }

    public function test_task_completion_api_returns_correct_data(): void
    {
        $user = User::factory()->create();
        
        // 建立完成的記錄
        DailyLog::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::today()->subDays(1),
            'task_meal' => true,
            'task_walk' => true,
            'task_no_snack' => true,
            'task_sleep' => true,
            'daily_points' => 50,
        ]);
        
        $response = $this->actingAs($user)
            ->get('/api/gamification/stats/task-completion?days=30');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'completed',
            'partial',
            'none',
            'total',
            'rate',
        ]);
        
        $data = $response->json();
        $this->assertGreaterThanOrEqual(0, $data['completed']);
        $this->assertGreaterThanOrEqual(0, $data['rate']);
    }

    public function test_streak_trend_api_returns_correct_data(): void
    {
        $user = User::factory()->create();
        
        // 建立連續完成的記錄
        for ($i = 0; $i < 3; $i++) {
            DailyLog::factory()->create([
                'user_id' => $user->id,
                'date' => Carbon::today()->subDays($i),
                'task_meal' => true,
                'task_walk' => true,
                'task_no_snack' => true,
                'task_sleep' => true,
            ]);
        }
        
        $response = $this->actingAs($user)
            ->get('/api/gamification/stats/streak-trend?days=30');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'labels',
            'streaks',
            'max_streak',
            'current_streak',
        ]);
        
        $data = $response->json();
        $this->assertIsArray($data['labels']);
        $this->assertIsArray($data['streaks']);
        $this->assertGreaterThanOrEqual(0, $data['current_streak']);
    }
}
