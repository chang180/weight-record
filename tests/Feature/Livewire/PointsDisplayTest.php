<?php

namespace Tests\Feature\Livewire;

use App\Livewire\PointsDisplay;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PointsDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_can_be_rendered(): void
    {
        $user = User::factory()->create([
            'total_points' => 100,
            'available_points' => 80,
        ]);

        Livewire::actingAs($user)
            ->test(PointsDisplay::class)
            ->assertSuccessful();
    }

    public function test_displays_available_points(): void
    {
        $user = User::factory()->create([
            'total_points' => 200,
            'available_points' => 150,
        ]);

        Livewire::actingAs($user)
            ->test(PointsDisplay::class)
            ->assertSet('availablePoints', 150)
            ->assertSet('totalPoints', 200);
    }

    public function test_updates_when_points_updated_event_received(): void
    {
        $user = User::factory()->create([
            'total_points' => 100,
            'available_points' => 50,
        ]);

        $component = Livewire::actingAs($user)
            ->test(PointsDisplay::class)
            ->assertSet('availablePoints', 50);

        // 更新用戶積分
        $user->update([
            'available_points' => 75,
        ]);

        // 觸發事件（會重新載入用戶積分）
        $component->dispatch('points-updated', points: 75)
            ->assertSet('availablePoints', 75);
    }

    public function test_updates_when_weight_recorded_event_received(): void
    {
        $user = User::factory()->create([
            'total_points' => 100,
            'available_points' => 50,
        ]);

        // 更新用戶積分
        $user->update([
            'total_points' => 120,
            'available_points' => 70,
        ]);

        Livewire::actingAs($user)
            ->test(PointsDisplay::class)
            ->dispatch('weight-recorded')
            ->assertSet('availablePoints', 70)
            ->assertSet('totalPoints', 120);
    }

    public function test_displays_zero_when_user_has_no_points(): void
    {
        $user = User::factory()->create([
            'total_points' => 0,
            'available_points' => 0,
        ]);

        Livewire::actingAs($user)
            ->test(PointsDisplay::class)
            ->assertSet('availablePoints', 0)
            ->assertSet('totalPoints', 0);
    }

    public function test_handles_points_updated_with_different_values(): void
    {
        $user = User::factory()->create([
            'total_points' => 100,
            'available_points' => 50,
        ]);

        $component = Livewire::actingAs($user)
            ->test(PointsDisplay::class)
            ->assertSet('availablePoints', 50);

        // 測試多次更新（每次更新後會重新載入用戶積分）
        $user->update(['available_points' => 60]);
        $component->dispatch('points-updated', points: 60)
            ->assertSet('availablePoints', 60);

        $user->update(['available_points' => 80]);
        $component->dispatch('points-updated', points: 80)
            ->assertSet('availablePoints', 80);

        $user->update(['available_points' => 30]);
        $component->dispatch('points-updated', points: 30)
            ->assertSet('availablePoints', 30);
    }
}
