<?php

namespace Tests\Feature\Livewire;

use App\Livewire\WeeklyReport;
use App\Models\User;
use App\Models\DailyLog;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WeeklyReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_can_be_rendered(): void
    {
        $user = User::factory()->create();
        assert($user instanceof User);

        Livewire::actingAs($user)
            ->test(WeeklyReport::class)
            ->assertSuccessful();
    }

    public function test_displays_current_week_by_default(): void
    {
        $user = User::factory()->create();
        assert($user instanceof User);

        Livewire::actingAs($user)
            ->test(WeeklyReport::class)
            ->assertSee(Carbon::today()->startOfWeek()->format('Y年m月d日'));
    }

    public function test_calculates_week_statistics(): void
    {
        $user = User::factory()->create();
        assert($user instanceof User);

        $weekStart = Carbon::today()->startOfWeek();
        
        DailyLog::factory()->create([
            'user_id' => $user->id,
            'date' => $weekStart->copy()->addDays(1),
            'weight' => 75.0,
            'task_meal' => true,
            'task_walk' => true,
            'task_no_snack' => true,
            'task_sleep' => true,
            'daily_points' => 50,
        ]);

        DailyLog::factory()->create([
            'user_id' => $user->id,
            'date' => $weekStart->copy()->addDays(5),
            'weight' => 74.5,
            'daily_points' => 30,
        ]);

        Livewire::actingAs($user)
            ->test(WeeklyReport::class)
            ->assertSet('tasksCompleted.completed_days', 1)
            ->assertSet('pointsEarned.total', 80);
    }

    public function test_navigation_to_previous_week(): void
    {
        $user = User::factory()->create();
        assert($user instanceof User);

        Livewire::actingAs($user)
            ->test(WeeklyReport::class)
            ->call('previousWeek')
            ->assertSet('weekStart', Carbon::today()->startOfWeek()->subWeek());
    }

    public function test_navigation_to_next_week(): void
    {
        $user = User::factory()->create();
        assert($user instanceof User);

        Livewire::actingAs($user)
            ->test(WeeklyReport::class)
            ->call('nextWeek')
            ->assertSet('weekStart', Carbon::today()->startOfWeek()->addWeek());
    }

    public function test_calculates_weight_change(): void
    {
        $user = User::factory()->create();
        assert($user instanceof User);

        $weekStart = Carbon::today()->startOfWeek();
        
        DailyLog::factory()->create([
            'user_id' => $user->id,
            'date' => $weekStart,
            'weight' => 75.0,
        ]);

        DailyLog::factory()->create([
            'user_id' => $user->id,
            'date' => $weekStart->copy()->endOfWeek(),
            'weight' => 74.5,
        ]);

        Livewire::actingAs($user)
            ->test(WeeklyReport::class)
            ->assertSet('startWeight', 75.0)
            ->assertSet('endWeight', 74.5);
    }

    public function test_handles_empty_week(): void
    {
        $user = User::factory()->create();
        assert($user instanceof User);

        Livewire::actingAs($user)
            ->test(WeeklyReport::class)
            ->assertSet('tasksCompleted.total_days', 0)
            ->assertSet('pointsEarned.total', 0);
    }
}
