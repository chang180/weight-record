<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Weight;
use App\Models\DailyLog;
use App\Livewire\WeightRecordForm;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WeightDailyLogSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'AchievementSeeder']);
    }

    /**
     * 測試：在 dashboard 新增今日體重時，應該同步創建 daily_log 記錄
     */
    public function test_creating_today_weight_creates_daily_log(): void
    {
        $user = User::factory()->create([
            'total_points' => 0,
            'available_points' => 0,
        ]);

        // 在 dashboard 新增今日體重
        Livewire::actingAs($user)
            ->test(WeightRecordForm::class)
            ->set('weight', 75.0)
            ->set('record_at', Carbon::today()->format('Y-m-d'))
            ->set('note', '測試備註')
            ->call('store')
            ->assertHasNoErrors();

        // 檢查 weights 表有記錄
        $this->assertDatabaseHas('weights', [
            'user_id' => $user->id,
            'weight' => 75.0,
        ]);

        // 重點：檢查 daily_logs 表也應該有今天的記錄
        $dailyLog = DailyLog::where('user_id', $user->id)
            ->where('date', Carbon::today())
            ->first();

        $this->assertNotNull($dailyLog, 'DailyLog 應該被創建');
        $this->assertEquals(75.0, $dailyLog->weight, 'DailyLog 的體重應該被同步');
        $this->assertEquals('測試備註', $dailyLog->notes, 'DailyLog 的備註應該被同步');
    }

    /**
     * 測試：新增過去的體重記錄時，不應該創建 daily_log
     */
    public function test_creating_past_weight_does_not_create_daily_log(): void
    {
        $user = User::factory()->create();

        // 新增昨天的體重記錄
        Livewire::actingAs($user)
            ->test(WeightRecordForm::class)
            ->set('weight', 75.0)
            ->set('record_at', Carbon::yesterday()->format('Y-m-d'))
            ->call('store')
            ->assertHasNoErrors();

        // 檢查 weights 表有記錄
        $this->assertDatabaseHas('weights', [
            'user_id' => $user->id,
            'weight' => 75.0,
        ]);

        // 檢查 daily_logs 表不應該有昨天的記錄（因為我們只同步今天的）
        $dailyLog = DailyLog::where('user_id', $user->id)
            ->where('date', Carbon::yesterday())
            ->first();

        $this->assertNull($dailyLog, '過去的體重記錄不應該創建 DailyLog');
    }

    /**
     * 測試：在任務頁面可以識別從 dashboard 新增的體重記錄
     */
    public function test_daily_task_page_recognizes_weight_from_dashboard(): void
    {
        $user = User::factory()->create([
            'total_points' => 0,
            'available_points' => 0,
        ]);

        // 在 dashboard 新增今日體重
        Livewire::actingAs($user)
            ->test(WeightRecordForm::class)
            ->set('weight', 75.0)
            ->set('record_at', Carbon::today()->format('Y-m-d'))
            ->call('store');

        // 前往任務頁面，檢查是否能識別今日記錄
        $response = $this->actingAs($user)->get('/daily-logs');
        $response->assertStatus(200);

        // 檢查 daily_log 已被創建
        $dailyLog = DailyLog::where('user_id', $user->id)
            ->where('date', Carbon::today())
            ->first();

        $this->assertNotNull($dailyLog, '任務頁面應該能找到今日的 DailyLog');
        $this->assertEquals(75.0, $dailyLog->weight);
    }
}
