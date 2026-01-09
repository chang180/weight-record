<?php

namespace Tests\Feature\Livewire;

use App\Livewire\WeightRecordList;
use App\Models\User;
use App\Models\Weight;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WeightRecordListTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_can_be_rendered(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(WeightRecordList::class)
            ->assertSuccessful();
    }

    public function test_displays_weight_records(): void
    {
        $user = User::factory()->create();
        Weight::factory()->count(5)->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(WeightRecordList::class)
            ->assertSee('所有體重記錄')
            ->assertViewHas('weights', function ($weights) {
                return $weights->count() === 5;
            });
    }

    public function test_pagination_works(): void
    {
        $user = User::factory()->create();
        Weight::factory()->count(20)->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(WeightRecordList::class)
            ->assertViewHas('weights', function ($weights) {
                return $weights->count() === 15; // 每頁 15 筆
            });
    }

    public function test_date_filter_works(): void
    {
        $user = User::factory()->create();
        
        // 創建不同日期的記錄
        $oldDate = Carbon::today()->subDays(10)->format('Y-m-d');
        $middleDate = Carbon::today()->subDays(5)->format('Y-m-d');
        $todayDate = Carbon::today()->format('Y-m-d');
        
        Weight::factory()->create([
            'user_id' => $user->id,
            'record_at' => $oldDate,
        ]);
        
        Weight::factory()->create([
            'user_id' => $user->id,
            'record_at' => $middleDate,
        ]);
        
        Weight::factory()->create([
            'user_id' => $user->id,
            'record_at' => $todayDate,
        ]);

        // 篩選最近 7 天
        Livewire::actingAs($user)
            ->test(WeightRecordList::class)
            ->set('start_date', Carbon::today()->subDays(7)->format('Y-m-d'))
            ->set('end_date', Carbon::today()->format('Y-m-d'))
            ->assertSee($middleDate) // 應該看到 5 天前的記錄日期
            ->assertSee($todayDate) // 應該看到今天的記錄日期
            ->assertDontSee($oldDate); // 不應該看到 10 天前的記錄日期
    }

    public function test_reset_filters_works(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(WeightRecordList::class)
            ->set('start_date', '2024-01-01')
            ->set('end_date', '2024-12-31')
            ->call('resetFilters')
            ->assertSet('start_date', '')
            ->assertSet('end_date', '');
    }

    public function test_user_can_edit_weight_record(): void
    {
        $user = User::factory()->create();
        $weight = Weight::factory()->create([
            'user_id' => $user->id,
            'weight' => 75.0,
            'note' => '原始備註',
        ]);

        Livewire::actingAs($user)
            ->test(WeightRecordList::class)
            ->call('edit', $weight->id)
            ->assertSet('editingId', $weight->id)
            ->assertSet('editingWeight', 75.0)
            ->assertSet('editingNote', '原始備註');
    }

    public function test_user_can_update_weight_record(): void
    {
        $user = User::factory()->create();
        $weight = Weight::factory()->create([
            'user_id' => $user->id,
            'weight' => 75.0,
            'note' => '原始備註',
        ]);

        Livewire::actingAs($user)
            ->test(WeightRecordList::class)
            ->call('edit', $weight->id)
            ->set('editingWeight', 74.5)
            ->set('editingNote', '更新後的備註')
            ->call('update')
            ->assertHasNoErrors();

        $weight->refresh();
        $this->assertEquals(74.5, $weight->weight);
        $this->assertEquals('更新後的備註', $weight->note);
    }

    public function test_update_validation_works(): void
    {
        $user = User::factory()->create();
        $weight = Weight::factory()->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(WeightRecordList::class)
            ->call('edit', $weight->id)
            ->set('editingWeight', 19) // 低於最小值
            ->call('update')
            ->assertHasErrors(['editingWeight']);
    }

    public function test_update_prevents_duplicate_dates(): void
    {
        $user = User::factory()->create();
        
        $weight1 = Weight::factory()->create([
            'user_id' => $user->id,
            'record_at' => Carbon::today()->subDays(5)->format('Y-m-d'),
        ]);
        
        $weight2 = Weight::factory()->create([
            'user_id' => $user->id,
            'record_at' => Carbon::today()->subDays(3)->format('Y-m-d'),
        ]);

        // 嘗試將第二筆記錄的日期改為與第一筆相同
        Livewire::actingAs($user)
            ->test(WeightRecordList::class)
            ->call('edit', $weight2->id)
            ->set('editingRecordAt', Carbon::today()->subDays(5)->format('Y-m-d'))
            ->call('update')
            ->assertHasErrors(['editingRecordAt']);
    }

    public function test_user_can_cancel_edit(): void
    {
        $user = User::factory()->create();
        $weight = Weight::factory()->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(WeightRecordList::class)
            ->call('edit', $weight->id)
            ->assertSet('editingId', $weight->id)
            ->call('cancelEdit')
            ->assertSet('editingId', null);
    }

    public function test_user_can_confirm_delete(): void
    {
        $user = User::factory()->create();
        $weight = Weight::factory()->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(WeightRecordList::class)
            ->call('confirmDelete', $weight->id)
            ->assertSet('deletingId', $weight->id)
            ->assertSet('showDeleteModal', true);
    }

    public function test_user_can_delete_weight_record(): void
    {
        $user = User::factory()->create();
        $weight = Weight::factory()->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(WeightRecordList::class)
            ->call('confirmDelete', $weight->id)
            ->call('delete')
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('weights', ['id' => $weight->id]);
    }

    public function test_delete_today_record_deducts_points(): void
    {
        $user = User::factory()->create([
            'total_points' => 100,
            'available_points' => 100,
        ]);
        
        $weight = Weight::factory()->create([
            'user_id' => $user->id,
            'record_at' => Carbon::today()->format('Y-m-d'),
            'created_at' => Carbon::today(),
        ]);

        Livewire::actingAs($user)
            ->test(WeightRecordList::class)
            ->call('confirmDelete', $weight->id)
            ->call('delete');

        $user->refresh();
        // 應該扣除 20 積分（防刷分機制）
        $this->assertEquals(80, $user->available_points);
    }

    public function test_delete_old_record_does_not_deduct_points(): void
    {
        $user = User::factory()->create([
            'total_points' => 100,
            'available_points' => 100,
        ]);
        
        $weight = Weight::factory()->create([
            'user_id' => $user->id,
            'record_at' => Carbon::yesterday()->format('Y-m-d'),
            'created_at' => Carbon::yesterday(),
        ]);

        Livewire::actingAs($user)
            ->test(WeightRecordList::class)
            ->call('confirmDelete', $weight->id)
            ->call('delete');

        $user->refresh();
        // 不應該扣除積分
        $this->assertEquals(100, $user->available_points);
    }

    public function test_user_can_cancel_delete(): void
    {
        $user = User::factory()->create();
        $weight = Weight::factory()->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(WeightRecordList::class)
            ->call('confirmDelete', $weight->id)
            ->assertSet('showDeleteModal', true)
            ->call('cancelDelete')
            ->assertSet('showDeleteModal', false)
            ->assertSet('deletingId', null);
    }

    public function test_user_cannot_edit_other_users_records(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $weight = Weight::factory()->create(['user_id' => $user2->id]);

        Livewire::actingAs($user1)
            ->test(WeightRecordList::class)
            ->call('edit', $weight->id)
            ->assertSet('editingId', null);
    }

    public function test_user_cannot_delete_other_users_records(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $weight = Weight::factory()->create(['user_id' => $user2->id]);

        Livewire::actingAs($user1)
            ->test(WeightRecordList::class)
            ->call('confirmDelete', $weight->id)
            ->call('delete');

        // 記錄應該仍然存在
        $this->assertDatabaseHas('weights', ['id' => $weight->id]);
    }

    public function test_empty_state_displayed_when_no_records(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(WeightRecordList::class)
            ->assertSee('目前還沒有記錄')
            ->assertSee('新增第一筆記錄');
    }
}
