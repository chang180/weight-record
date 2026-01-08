<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Weight;
use App\Models\Achievement;
use App\Services\PointsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeightRecordingRewardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // 執行成就 seeder 以建立成就資料
        $this->artisan('db:seed', ['--class' => 'AchievementSeeder']);
    }

    public function test_user_earns_points_when_recording_weight(): void
    {
        $user = User::factory()->create([
            'total_points' => 0,
            'available_points' => 0,
        ]);

        $response = $this->actingAs($user)
            ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
            ->post('/weights', [
                'weight' => 75.0,
                'record_at' => now()->format('Y-m-d'),
            ]);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('recording_reward', 20);

        $user->refresh();
        $this->assertEquals(20, $user->total_points);
        $this->assertEquals(20, $user->available_points);
    }

    public function test_user_gets_deducted_points_for_missed_days(): void
    {
        $user = User::factory()->create([
            'total_points' => 100,
            'available_points' => 100,
        ]);

        // 創建 3 天前的記錄
        Weight::factory()->create([
            'user_id' => $user->id,
            'record_at' => Carbon::today()->subDays(3)->format('Y-m-d'),
        ]);

        // 現在記錄體重（漏記了 2 天）
        $response = $this->actingAs($user)
            ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
            ->post('/weights', [
                'weight' => 74.0,
                'record_at' => Carbon::today()->format('Y-m-d'),
            ]);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('points_deducted');
        $response->assertSessionHas('deduction_reason');
        $response->assertSessionHas('recording_reward', 20);

        $user->refresh();
        // 100 (原有) - 20 (漏記 2 天扣分) + 20 (記錄獎勵) = 100
        $this->assertEquals(100, $user->available_points);
        $this->assertEquals(120, $user->total_points);
    }

    public function test_points_deduction_does_not_go_below_zero(): void
    {
        $user = User::factory()->create([
            'total_points' => 50,
            'available_points' => 15, // 可用積分很少
        ]);

        // 創建 5 天前的記錄（會扣 40 分，但只有 15 分可扣）
        Weight::factory()->create([
            'user_id' => $user->id,
            'record_at' => Carbon::today()->subDays(5)->format('Y-m-d'),
        ]);

        $response = $this->actingAs($user)
            ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
            ->post('/weights', [
                'weight' => 73.0,
                'record_at' => Carbon::today()->format('Y-m-d'),
            ]);

        $user->refresh();
        // 15 (原有) - 15 (實際扣分) + 20 (記錄獎勵) = 20
        $this->assertEquals(0, $user->available_points - 20); // 扣完後剩 0，但加上記錄獎勵
        $this->assertGreaterThanOrEqual(0, $user->available_points);
    }

    public function test_no_deduction_for_first_weight_record(): void
    {
        $user = User::factory()->create([
            'total_points' => 0,
            'available_points' => 0,
        ]);

        $response = $this->actingAs($user)
            ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
            ->post('/weights', [
                'weight' => 75.0,
                'record_at' => now()->format('Y-m-d'),
            ]);

        $response->assertRedirect('/dashboard');
        $response->assertSessionMissing('points_deducted');
        $response->assertSessionHas('recording_reward', 20);
    }

    public function test_recording_reward_and_deduction_both_shown(): void
    {
        $user = User::factory()->create([
            'total_points' => 100,
            'available_points' => 100,
        ]);

        Weight::factory()->create([
            'user_id' => $user->id,
            'record_at' => Carbon::today()->subDays(2)->format('Y-m-d'),
        ]);

        $response = $this->actingAs($user)
            ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
            ->post('/weights', [
                'weight' => 74.5,
                'record_at' => Carbon::today()->format('Y-m-d'),
            ]);

        // 應該同時有扣分和獎勵的通知
        $response->assertSessionHas('points_deducted');
        $response->assertSessionHas('recording_reward', 20);
    }

    public function test_daily_recorder_7_achievement_unlocks(): void
    {
        $user = User::factory()->create([
            'total_points' => 0,
            'available_points' => 0,
        ]);

        // 創建連續 6 天的記錄（從 6 天前到今天的前一天）
        for ($i = 6; $i >= 1; $i--) {
            Weight::factory()->create([
                'user_id' => $user->id,
                'record_at' => Carbon::today()->subDays($i)->format('Y-m-d'),
            ]);
        }

        $initialPoints = $user->total_points;
        $initialAvailablePoints = $user->available_points;

        // 第 7 天記錄（今天）
        $response = $this->actingAs($user)
            ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
            ->post('/weights', [
                'weight' => 73.0,
                'record_at' => Carbon::today()->format('Y-m-d'),
            ]);

        $response->assertSessionHas('achievement');
        
        $achievement = Achievement::where('code', 'daily_recorder_7')->first();
        $this->assertNotNull($achievement);
        
        $user->refresh();
        
        // 確認成就已解鎖
        $this->assertTrue(
            $user->achievements()->where('achievements.id', $achievement->id)->exists(),
            '成就應該已被解鎖'
        );
        
        // 檢查積分：7 次記錄 * 20 積分 = 140，加上成就獎勵 50 = 190
        // 但實際上只有今天的記錄會給積分（20），加上成就獎勵（50）= 70
        // 因為歷史記錄是在測試中直接創建的，不會觸發記錄獎勵
        $this->assertGreaterThanOrEqual($initialPoints + 70, $user->total_points);
        $this->assertGreaterThanOrEqual($initialAvailablePoints + 70, $user->available_points);
    }

    public function test_daily_recorder_30_achievement_unlocks(): void
    {
        $user = User::factory()->create([
            'total_points' => 0,
            'available_points' => 0,
        ]);

        // 創建連續 29 天的記錄（從 29 天前到今天的前一天）
        for ($i = 29; $i >= 1; $i--) {
            Weight::factory()->create([
                'user_id' => $user->id,
                'record_at' => Carbon::today()->subDays($i)->format('Y-m-d'),
            ]);
        }

        $initialPoints = $user->total_points;
        $initialAvailablePoints = $user->available_points;

        // 第 30 天記錄（今天）
        $response = $this->actingAs($user)
            ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
            ->post('/weights', [
                'weight' => 70.0,
                'record_at' => Carbon::today()->format('Y-m-d'),
            ]);

        $response->assertSessionHas('achievement');
        
        $achievement = Achievement::where('code', 'daily_recorder_30')->first();
        $this->assertNotNull($achievement);
        
        $user->refresh();
        
        // 確認成就已解鎖
        $this->assertTrue(
            $user->achievements()->where('achievements.id', $achievement->id)->exists(),
            '成就應該已被解鎖'
        );
        
        // 檢查積分：今天的記錄 20 + 7天成就 50 + 30天成就 300 = 370
        // 因為歷史記錄是在測試中直接創建的，不會觸發記錄獎勵
        $this->assertGreaterThanOrEqual($initialPoints + 370, $user->total_points);
        $this->assertGreaterThanOrEqual($initialAvailablePoints + 370, $user->available_points);
    }

    public function test_achievement_does_not_unlock_twice(): void
    {
        $user = User::factory()->create();
        $achievement = Achievement::where('code', 'daily_recorder_7')->first();
        
        // 手動解鎖成就
        $user->achievements()->attach($achievement->id, [
            'unlocked_at' => now(),
        ]);

        // 創建連續 6 天的記錄
        for ($i = 6; $i >= 1; $i--) {
            Weight::factory()->create([
                'user_id' => $user->id,
                'record_at' => Carbon::today()->subDays($i)->format('Y-m-d'),
            ]);
        }

        $initialPoints = $user->total_points;

        // 第 7 天記錄（不應該再次解鎖）
        $response = $this->actingAs($user)
            ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
            ->post('/weights', [
                'weight' => 73.0,
                'record_at' => Carbon::today()->format('Y-m-d'),
            ]);

        $user->refresh();
        // 應該只有記錄獎勵，沒有成就獎勵
        $this->assertEquals($initialPoints + 20, $user->total_points);
    }

    public function test_streak_resets_when_there_is_gap(): void
    {
        $user = User::factory()->create();

        // 創建 10 天前和 8 天前的記錄（中間有間斷）
        Weight::factory()->create([
            'user_id' => $user->id,
            'record_at' => Carbon::today()->subDays(10)->format('Y-m-d'),
        ]);

        Weight::factory()->create([
            'user_id' => $user->id,
            'record_at' => Carbon::today()->subDays(8)->format('Y-m-d'),
        ]);

        // 創建連續 6 天的記錄（從 7 天前開始）
        for ($i = 6; $i >= 0; $i--) {
            Weight::factory()->create([
                'user_id' => $user->id,
                'record_at' => Carbon::today()->subDays($i)->format('Y-m-d'),
            ]);
        }

        // 今天記錄（連續 7 天）
        $response = $this->actingAs($user)
            ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
            ->post('/weights', [
                'weight' => 72.0,
                'record_at' => Carbon::today()->format('Y-m-d'),
            ]);

        // 應該解鎖 7 天成就（從最新連續的 7 天計算）
        $response->assertSessionHas('achievement');
        $achievement = Achievement::where('code', 'daily_recorder_7')->first();
        $this->assertTrue($user->achievements()->where('achievements.id', $achievement->id)->exists());
    }
}
