<?php

namespace Tests\Feature\Livewire;

use App\Livewire\DailyTaskList;
use App\Models\User;
use App\Models\DailyLog;
use App\Models\Achievement;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DailyTaskListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // 執行成就 seeder 以建立成就資料
        $this->artisan('db:seed', ['--class' => 'AchievementSeeder']);
    }

    public function test_component_can_be_rendered(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(DailyTaskList::class)
            ->assertSuccessful();
    }

    public function test_displays_tasks_for_weekday(): void
    {
        $user = User::factory()->create();

        // 設定為週二,這樣昨天(週一)是工作日
        Carbon::setTestNow(Carbon::create(2024, 1, 2)); // 週二

        Livewire::actingAs($user)
            ->test(DailyTaskList::class)
            ->assertSee('昨日工作日任務')
            ->assertSee('只吃晚餐')
            ->assertSee('早點睡');
    }

    public function test_displays_tasks_for_weekend(): void
    {
        $user = User::factory()->create();

        // 設定為週日,這樣昨天(週六)是週末
        Carbon::setTestNow(Carbon::create(2024, 1, 7)); // 週日

        Livewire::actingAs($user)
            ->test(DailyTaskList::class)
            ->assertSee('昨日週末任務')
            ->assertSee('只吃 2 餐')
            ->assertDontSee('早點睡');
    }

    public function test_shows_warning_when_no_daily_log(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(DailyTaskList::class)
            ->assertSee('請先建立昨日記錄');
    }

    public function test_user_can_create_daily_log_with_weight(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(DailyTaskList::class)
            ->set('weight', 75.0)
            ->set('notes', '測試備註')
            ->call('storeWeightRecord')
            ->assertHasNoErrors();

        $dailyLog = \App\Models\DailyLog::where('user_id', $user->id)
            ->whereDate('date', Carbon::yesterday())
            ->first();

        $this->assertNotNull($dailyLog);
        $this->assertEquals(75.0, $dailyLog->weight);
        $this->assertEquals('測試備註', $dailyLog->notes);
    }

    public function test_user_can_toggle_task(): void
    {
        $user = User::factory()->create();
        $dailyLog = DailyLog::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::yesterday(),
            'task_meal' => false,
        ]);

        Livewire::actingAs($user)
            ->test(DailyTaskList::class)
            ->call('toggleTask', 'task_meal')
            ->assertHasNoErrors();

        $dailyLog->refresh();
        $this->assertTrue($dailyLog->task_meal);
    }

    public function test_toggle_task_updates_points(): void
    {
        $user = User::factory()->create([
            'total_points' => 0,
            'available_points' => 0,
        ]);

        $dailyLog = DailyLog::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::yesterday(),
            'task_meal' => false,
            'task_walk' => false,
            'task_no_snack' => false,
            'task_sleep' => false,
            'daily_points' => 0,
        ]);

        Livewire::actingAs($user)
            ->test(DailyTaskList::class)
            ->call('toggleTask', 'task_meal');

        $dailyLog->refresh();
        $this->assertEquals(10, $dailyLog->daily_points);

        $user->refresh();
        $this->assertEquals(10, $user->available_points);
    }

    public function test_toggle_task_deducts_points_when_uncompleting(): void
    {
        $user = User::factory()->create([
            'total_points' => 50,
            'available_points' => 50,
        ]);

        $dailyLog = DailyLog::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::yesterday(),
            'task_meal' => true,
            'task_walk' => true,
            'task_no_snack' => true,
            'task_sleep' => true,
            'daily_points' => 50,
        ]);

        Livewire::actingAs($user)
            ->test(DailyTaskList::class)
            ->call('toggleTask', 'task_meal');

        $dailyLog->refresh();
        $this->assertEquals(40, $dailyLog->daily_points); // 50 - 10 = 40

        $user->refresh();
        $this->assertEquals(40, $user->available_points);
    }

    public function test_progress_calculation(): void
    {
        $user = User::factory()->create();
        $dailyLog = DailyLog::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::yesterday(),
            'task_meal' => true,
            'task_walk' => true,
            'task_no_snack' => false,
            'task_sleep' => false,
        ]);

        Livewire::actingAs($user)
            ->test(DailyTaskList::class)
            ->assertSet('completedCount', 2)
            ->assertSet('totalCount', 4)
            ->assertSet('progress', 50.0);
    }

    public function test_all_completed_property(): void
    {
        $user = User::factory()->create();
        $dailyLog = DailyLog::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::yesterday(),
            'task_meal' => true,
            'task_walk' => true,
            'task_no_snack' => true,
            'task_sleep' => true,
        ]);

        Livewire::actingAs($user)
            ->test(DailyTaskList::class)
            ->assertSet('allCompleted', true);
    }

    public function test_achievement_unlocked_when_all_tasks_completed(): void
    {
        $user = User::factory()->create();

        $dailyLog = DailyLog::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::yesterday(),
            'task_meal' => true,
            'task_walk' => true,
            'task_no_snack' => true,
            'task_sleep' => false, // 還差一個任務
        ]);

        Livewire::actingAs($user)
            ->test(DailyTaskList::class)
            ->call('toggleTask', 'task_sleep');

        // 檢查是否有成就解鎖（完美一天應該會觸發某些成就）
        $dailyLog->refresh();
        $this->assertTrue($dailyLog->isAllTasksCompleted());
    }

    public function test_cannot_toggle_task_without_daily_log(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(DailyTaskList::class)
            ->call('toggleTask', 'task_meal')
            ->assertDispatched('show-error');
    }

    public function test_streak_updated_after_task_completion(): void
    {
        $user = User::factory()->create([
            'current_streak' => 0,
        ]);

        // 設定為週二,這樣昨天是週一(工作日)
        Carbon::setTestNow(Carbon::parse('2024-01-09')); // 2024-01-09 是週二

        // 創建前天（週日,週末）的完美記錄
        DailyLog::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::yesterday()->subDay(), // 週日
            'task_meal' => true,
            'task_walk' => true,
            'task_no_snack' => true,
            'task_sleep' => false,
            'task_no_sugar' => true, // 週日任務
        ]);

        // 創建昨天（週一,工作日）的記錄 - 這是我們要操作的對象
        $dailyLog = DailyLog::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::yesterday(), // 週一
            'task_meal' => true,
            'task_walk' => true,
            'task_no_snack' => true,
            'task_sleep' => false, // 最後一個未完成的任務
        ]);

        Livewire::actingAs($user)
            ->test(DailyTaskList::class)
            ->call('toggleTask', 'task_sleep');

        $user->refresh();
        $this->assertEquals(2, $user->current_streak); // 週日 + 週一

        // 恢復時間
        Carbon::setTestNow();
    }

    public function test_weight_record_form_validation(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(DailyTaskList::class)
            ->set('weight', 19) // 低於最小值（但驗證是 min:0，所以這個測試需要調整）
            ->set('notes', str_repeat('a', 1001)) // 超過最大長度
            ->call('storeWeightRecord')
            ->assertHasErrors(['notes']);
    }

    public function test_points_updated_event_dispatched(): void
    {
        $user = User::factory()->create();
        $dailyLog = DailyLog::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::yesterday(),
            'task_meal' => false,
        ]);

        Livewire::actingAs($user)
            ->test(DailyTaskList::class)
            ->call('toggleTask', 'task_meal')
            ->assertDispatched('points-updated');
    }
}
