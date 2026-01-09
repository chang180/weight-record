<?php

namespace Tests\Feature\Livewire;

use App\Livewire\WeightRecordForm;
use App\Models\User;
use App\Models\Weight;
use App\Models\Achievement;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WeightRecordFormTest extends TestCase
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
            ->test(WeightRecordForm::class)
            ->assertSuccessful();
    }

    public function test_form_validation_works(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(WeightRecordForm::class)
            ->set('weight', '')
            ->set('record_at', '')
            ->call('store')
            ->assertHasErrors(['weight', 'record_at']);
    }

    public function test_weight_must_be_numeric(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(WeightRecordForm::class)
            ->set('weight', 'invalid')
            ->set('record_at', Carbon::today()->format('Y-m-d'))
            ->call('store')
            ->assertHasErrors(['weight']);
    }

    public function test_weight_must_be_within_range(): void
    {
        $user = User::factory()->create();

        // 測試最小值
        Livewire::actingAs($user)
            ->test(WeightRecordForm::class)
            ->set('weight', 19)
            ->set('record_at', Carbon::today()->format('Y-m-d'))
            ->call('store')
            ->assertHasErrors(['weight']);

        // 測試最大值
        Livewire::actingAs($user)
            ->test(WeightRecordForm::class)
            ->set('weight', 301)
            ->set('record_at', Carbon::today()->format('Y-m-d'))
            ->call('store')
            ->assertHasErrors(['weight']);
    }

    public function test_record_date_cannot_be_future(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(WeightRecordForm::class)
            ->set('weight', 75.0)
            ->set('record_at', Carbon::tomorrow()->format('Y-m-d'))
            ->call('store')
            ->assertHasErrors(['record_at']);
    }

    public function test_user_can_create_weight_record(): void
    {
        $user = User::factory()->create([
            'total_points' => 0,
            'available_points' => 0,
        ]);

        Livewire::actingAs($user)
            ->test(WeightRecordForm::class)
            ->set('weight', 75.0)
            ->set('record_at', Carbon::today()->format('Y-m-d'))
            ->set('note', '測試備註')
            ->call('store')
            ->assertHasNoErrors()
            ->assertSet('recordingReward', 20);

        $this->assertDatabaseHas('weights', [
            'user_id' => $user->id,
            'weight' => 75.0,
            'note' => '測試備註',
        ]);

        $user->refresh();
        $this->assertEquals(20, $user->total_points);
        $this->assertEquals(20, $user->available_points);
    }

    public function test_daily_record_limit_enforced(): void
    {
        $user = User::factory()->create();

        // 創建今天的記錄
        Weight::factory()->create([
            'user_id' => $user->id,
            'record_at' => Carbon::today()->format('Y-m-d'),
        ]);

        // 嘗試再次創建今天的記錄
        Livewire::actingAs($user)
            ->test(WeightRecordForm::class)
            ->set('weight', 74.0)
            ->set('record_at', Carbon::today()->format('Y-m-d'))
            ->call('store')
            ->assertHasErrors(['record_at']);
    }

    public function test_recording_reward_only_for_today(): void
    {
        $user = User::factory()->create([
            'total_points' => 0,
            'available_points' => 0,
        ]);

        // 記錄昨天的體重（不應該有獎勵）
        Livewire::actingAs($user)
            ->test(WeightRecordForm::class)
            ->set('weight', 75.0)
            ->set('record_at', Carbon::yesterday()->format('Y-m-d'))
            ->call('store')
            ->assertHasNoErrors()
            ->assertSet('recordingReward', null);

        $user->refresh();
        $this->assertEquals(0, $user->total_points);
    }

    public function test_points_deducted_for_missed_days(): void
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
        Livewire::actingAs($user)
            ->test(WeightRecordForm::class)
            ->set('weight', 74.0)
            ->set('record_at', Carbon::today()->format('Y-m-d'))
            ->call('store')
            ->assertHasNoErrors()
            ->assertSet('pointsDeducted', 20)
            ->assertSet('deductionReason', '漏記 2 天體重')
            ->assertSet('recordingReward', 20);

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

        Livewire::actingAs($user)
            ->test(WeightRecordForm::class)
            ->set('weight', 73.0)
            ->set('record_at', Carbon::today()->format('Y-m-d'))
            ->call('store')
            ->assertHasNoErrors()
            ->assertSet('pointsDeducted', 15)
            ->assertSet('pointsDebt', 25); // 還欠 25 積分

        $user->refresh();
        // 15 (原有) - 15 (實際扣分) + 20 (記錄獎勵) = 20
        $this->assertEquals(20, $user->available_points);
    }

    public function test_achievement_check_triggered(): void
    {
        $user = User::factory()->create([
            'total_points' => 0,
            'available_points' => 0,
        ]);

        // 創建連續 6 天的記錄
        for ($i = 6; $i >= 1; $i--) {
            Weight::factory()->create([
                'user_id' => $user->id,
                'record_at' => Carbon::today()->subDays($i)->format('Y-m-d'),
            ]);
        }

        // 第 7 天記錄（今天）
        Livewire::actingAs($user)
            ->test(WeightRecordForm::class)
            ->set('weight', 73.0)
            ->set('record_at', Carbon::today()->format('Y-m-d'))
            ->call('store')
            ->assertHasNoErrors();

        $achievement = Achievement::where('code', 'daily_recorder_7')->first();
        $this->assertNotNull($achievement);
        
        $user->refresh();
        
        // 確認成就已解鎖
        $this->assertTrue(
            $user->achievements()->where('achievements.id', $achievement->id)->exists(),
            '成就應該已被解鎖'
        );
    }

    public function test_form_resets_after_successful_submission(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(WeightRecordForm::class)
            ->set('weight', 75.0)
            ->set('record_at', Carbon::today()->format('Y-m-d'))
            ->set('note', '測試備註')
            ->call('store')
            ->assertSet('weight', '')
            ->assertSet('note', '')
            ->assertSet('record_at', Carbon::today()->format('Y-m-d'));
    }

    public function test_event_dispatched_after_recording(): void
    {
        $user = User::factory()->create([
            'total_points' => 0,
            'available_points' => 0,
        ]);

        Livewire::actingAs($user)
            ->test(WeightRecordForm::class)
            ->set('weight', 75.0)
            ->set('record_at', Carbon::today()->format('Y-m-d'))
            ->call('store')
            ->assertDispatched('weight-recorded');
    }

    public function test_note_is_optional(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(WeightRecordForm::class)
            ->set('weight', 75.0)
            ->set('record_at', Carbon::today()->format('Y-m-d'))
            ->set('note', '')
            ->call('store')
            ->assertHasNoErrors();

        // 空字串會被存為空字串，不是 null
        $this->assertDatabaseHas('weights', [
            'user_id' => $user->id,
            'weight' => 75.0,
        ]);
        
        $weight = Weight::where('user_id', $user->id)->first();
        $this->assertTrue($weight->note === null || $weight->note === '');
    }

    public function test_note_max_length_enforced(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(WeightRecordForm::class)
            ->set('weight', 75.0)
            ->set('record_at', Carbon::today()->format('Y-m-d'))
            ->set('note', str_repeat('a', 501))
            ->call('store')
            ->assertHasErrors(['note']);
    }
}
