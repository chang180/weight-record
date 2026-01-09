<?php

namespace Tests\Feature\Livewire;

use App\Livewire\MonthlyReport;
use App\Models\User;
use App\Models\DailyLog;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MonthlyReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_can_be_rendered(): void
    {
        $user = User::factory()->create();
        assert($user instanceof User);

        Livewire::actingAs($user)
            ->test(MonthlyReport::class)
            ->assertSuccessful();
    }

    public function test_displays_current_month_by_default(): void
    {
        $user = User::factory()->create();
        assert($user instanceof User);

        Livewire::actingAs($user)
            ->test(MonthlyReport::class)
            ->assertSee(Carbon::today()->startOfMonth()->format('Y年m月'));
    }

    public function test_calculates_month_statistics(): void
    {
        $user = User::factory()->create();
        assert($user instanceof User);

        $monthStart = Carbon::today()->startOfMonth();
        
        DailyLog::factory()->create([
            'user_id' => $user->id,
            'date' => $monthStart->copy()->addDays(5),
            'weight' => 75.0,
            'task_meal' => true,
            'task_walk' => true,
            'task_no_snack' => true,
            'task_sleep' => true,
            'daily_points' => 50,
        ]);

        DailyLog::factory()->create([
            'user_id' => $user->id,
            'date' => $monthStart->copy()->addDays(15),
            'task_meal' => true,
            'task_walk' => true,
            'task_no_snack' => false,
            'task_sleep' => false,
            'daily_points' => 30,
        ]);

        Livewire::actingAs($user)
            ->test(MonthlyReport::class)
            ->assertSet('tasksCompleted.completed_days', 1)
            ->assertSet('pointsEarned.total', 80);
    }

    public function test_navigation_to_previous_month(): void
    {
        $user = User::factory()->create();
        assert($user instanceof User);

        Livewire::actingAs($user)
            ->test(MonthlyReport::class)
            ->call('previousMonth')
            ->assertSet('monthStart', Carbon::today()->startOfMonth()->subMonth());
    }

    public function test_navigation_to_next_month(): void
    {
        $user = User::factory()->create();
        assert($user instanceof User);

        Livewire::actingAs($user)
            ->test(MonthlyReport::class)
            ->call('nextMonth')
            ->assertSet('monthStart', Carbon::today()->startOfMonth()->addMonth());
    }

    public function test_generates_highlights(): void
    {
        $user = User::factory()->create();
        assert($user instanceof User);

        $monthStart = Carbon::today()->startOfMonth();
        
        // 創建大量完成的記錄
        for ($i = 0; $i < 25; $i++) {
            DailyLog::factory()->create([
                'user_id' => $user->id,
                'date' => $monthStart->copy()->addDays($i),
                'task_meal' => true,
                'task_walk' => true,
                'task_no_snack' => true,
                'task_sleep' => true,
            ]);
        }

        Livewire::actingAs($user)
            ->test(MonthlyReport::class)
            ->assertSet('highlights', function ($highlights) {
                return count($highlights) > 0;
            });
    }

    public function test_generates_suggestions(): void
    {
        $user = User::factory()->create();
        assert($user instanceof User);

        Livewire::actingAs($user)
            ->test(MonthlyReport::class)
            ->assertSet('suggestions', function ($suggestions) {
                return count($suggestions) > 0;
            });
    }

    public function test_handles_empty_month(): void
    {
        $user = User::factory()->create();
        assert($user instanceof User);

        Livewire::actingAs($user)
            ->test(MonthlyReport::class)
            ->assertSet('tasksCompleted.total_days', 0)
            ->assertSet('pointsEarned.total', 0);
    }
}
