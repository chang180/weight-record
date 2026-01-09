<?php

namespace Tests\Feature\Livewire;

use App\Livewire\RewardShop;
use App\Models\Reward;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RewardShopTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_can_be_rendered(): void
    {
        $user = User::factory()->create([
            'available_points' => 1000,
        ]);

        Livewire::actingAs($user)
            ->test(RewardShop::class)
            ->assertSuccessful();
    }

    public function test_displays_rewards_list(): void
    {
        $user = User::factory()->create([
            'available_points' => 1000,
        ]);

        Livewire::actingAs($user)
            ->test(RewardShop::class)
            ->assertSee('放縱餐券')
            ->assertSee('小確幸')
            ->assertSee('親子時光')
            ->assertSee('犒賞自己')
            ->assertSee('大獎勵');
    }

    public function test_displays_available_points(): void
    {
        $user = User::factory()->create([
            'available_points' => 2500,
        ]);

        Livewire::actingAs($user)
            ->test(RewardShop::class)
            ->assertSet('availablePoints', 2500);
    }

    public function test_user_can_select_reward(): void
    {
        $user = User::factory()->create([
            'available_points' => 1000,
        ]);

        Livewire::actingAs($user)
            ->test(RewardShop::class)
            ->call('selectReward', 0)
            ->assertSet('showRedeemModal', true)
            ->assertSet('selectedReward.type', 'indulgence_meal');
    }

    public function test_user_can_close_modal(): void
    {
        $user = User::factory()->create([
            'available_points' => 1000,
        ]);

        Livewire::actingAs($user)
            ->test(RewardShop::class)
            ->call('selectReward', 0)
            ->assertSet('showRedeemModal', true)
            ->call('closeModal')
            ->assertSet('showRedeemModal', false)
            ->assertSet('selectedReward', null);
    }

    public function test_user_can_redeem_reward_with_sufficient_points(): void
    {
        $user = User::factory()->create([
            'total_points' => 1000,
            'available_points' => 1000,
        ]);

        Livewire::actingAs($user)
            ->test(RewardShop::class)
            ->call('selectReward', 0) // 放縱餐券 500 積分
            ->set('notes', '測試備註')
            ->call('redeem')
            ->assertHasNoErrors()
            ->assertSet('showRedeemModal', false)
            ->assertDispatched('points-updated')
            ->assertDispatched('reward-redeemed');

        $this->assertDatabaseHas('rewards', [
            'user_id' => $user->id,
            'reward_type' => 'indulgence_meal',
            'points_spent' => 500,
            'notes' => '測試備註',
        ]);

        $user->refresh();
        $this->assertEquals(500, $user->available_points);
    }

    public function test_user_cannot_redeem_reward_with_insufficient_points(): void
    {
        $user = User::factory()->create([
            'total_points' => 100,
            'available_points' => 100,
        ]);

        Livewire::actingAs($user)
            ->test(RewardShop::class)
            ->call('selectReward', 0) // 放縱餐券 500 積分
            ->call('redeem')
            ->assertHasErrors(['points']);

        $this->assertDatabaseMissing('rewards', [
            'user_id' => $user->id,
        ]);
    }

    public function test_redeem_validates_notes_length(): void
    {
        $user = User::factory()->create([
            'available_points' => 1000,
        ]);

        Livewire::actingAs($user)
            ->test(RewardShop::class)
            ->call('selectReward', 0)
            ->set('notes', str_repeat('a', 501))
            ->call('redeem')
            ->assertHasErrors(['notes']);
    }

    public function test_redeem_without_notes(): void
    {
        $user = User::factory()->create([
            'total_points' => 1000,
            'available_points' => 1000,
        ]);

        Livewire::actingAs($user)
            ->test(RewardShop::class)
            ->call('selectReward', 0)
            ->set('notes', '')
            ->call('redeem')
            ->assertHasNoErrors();

        $reward = Reward::where('user_id', $user->id)->first();
        $this->assertNull($reward->notes);
    }

    public function test_redeem_updates_available_points(): void
    {
        $user = User::factory()->create([
            'total_points' => 2000,
            'available_points' => 2000,
        ]);

        Livewire::actingAs($user)
            ->test(RewardShop::class)
            ->call('selectReward', 1) // 小確幸 1000 積分
            ->call('redeem')
            ->assertSet('availablePoints', 1000);

        $user->refresh();
        $this->assertEquals(1000, $user->available_points);
    }

    public function test_redeem_creates_reward_record(): void
    {
        $user = User::factory()->create([
            'available_points' => 3000,
        ]);

        Livewire::actingAs($user)
            ->test(RewardShop::class)
            ->call('selectReward', 2) // 親子時光 2000 積分
            ->set('notes', '帶家人去吃大餐')
            ->call('redeem');

        $this->assertDatabaseHas('rewards', [
            'user_id' => $user->id,
            'reward_type' => 'family_time',
            'reward_name' => '親子時光',
            'points_spent' => 2000,
            'notes' => '帶家人去吃大餐',
        ]);
    }

    public function test_cannot_redeem_without_selected_reward(): void
    {
        $user = User::factory()->create([
            'available_points' => 1000,
        ]);

        Livewire::actingAs($user)
            ->test(RewardShop::class)
            ->set('selectedReward', null)
            ->call('redeem');

        // 應該不會有任何錯誤，但也不會創建記錄
        $this->assertDatabaseMissing('rewards', [
            'user_id' => $user->id,
        ]);
    }

    public function test_redeem_dispatches_events(): void
    {
        $user = User::factory()->create([
            'available_points' => 1000,
        ]);

        Livewire::actingAs($user)
            ->test(RewardShop::class)
            ->call('selectReward', 0)
            ->call('redeem')
            ->assertDispatched('points-updated')
            ->assertDispatched('reward-redeemed', reward: '放縱餐券');
    }
}
