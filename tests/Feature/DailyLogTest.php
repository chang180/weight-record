<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\DailyLog;
use App\Services\DailyTaskService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_daily_log_page(): void
    {
        $user = User::factory()->create();
        assert($user instanceof User);

        $response = $this->actingAs($user)->get('/daily-logs');

        $response->assertStatus(200);
        $response->assertViewIs('daily-log.index');
    }

    public function test_user_can_create_daily_log(): void
    {
        $user = User::factory()->create();
        assert($user instanceof User);

        $response = $this->actingAs($user)
            ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
            ->post('/daily-logs', [
                'weight' => 75.0,
                'notes' => '測試備註',
            ]);

        $response->assertRedirect('/daily-logs');
        
        $dailyLog = DailyLog::where('user_id', $user->id)
            ->whereDate('date', Carbon::today())
            ->first();
        
        $this->assertNotNull($dailyLog);
        $this->assertEquals(75.0, $dailyLog->weight);
        $this->assertEquals('測試備註', $dailyLog->notes);
    }

    public function test_user_can_update_daily_log(): void
    {
        $user = User::factory()->create();
        assert($user instanceof User);
        $dailyLog = DailyLog::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'weight' => 75.0,
        ]);

        $response = $this->actingAs($user)
            ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
            ->post('/daily-logs', [
                'weight' => 74.5,
                'notes' => '更新後的備註',
            ]);

        $response->assertRedirect('/daily-logs');
        $dailyLog->refresh();
        $this->assertEquals(74.5, $dailyLog->weight);
        $this->assertEquals('更新後的備註', $dailyLog->notes);
    }

    public function test_daily_log_calculates_points(): void
    {
        $user = User::factory()->create();
        $dailyLog = DailyLog::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'task_meal' => true,
            'task_walk' => true,
            'task_no_snack' => true,
            'task_sleep' => true,
        ]);

        // 手動計算積分以驗證邏輯
        $taskService = app(DailyTaskService::class);
        $calculatedPoints = $taskService->calculateDailyPoints($dailyLog);
        
        $this->assertEquals(50, $calculatedPoints); // 10 + 20 + 10 + 10
    }

    public function test_weekly_points_calculated_on_sunday(): void
    {
        $user = User::factory()->create();
        assert($user instanceof User);
        
        // 設定為週日
        Carbon::setTestNow(Carbon::create(2024, 1, 7)); // 週日
        
        $dailyLog = DailyLog::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
        ]);

        $response = $this->actingAs($user)
            ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
            ->post('/daily-logs', [
                'weight' => 75.0,
            ]);

        $dailyLog->refresh();
        // 週任務積分應該被計算（如果滿足條件）
        $this->assertNotNull($dailyLog->weekly_points);
        
        // 恢復時間
        Carbon::setTestNow();
    }
}
