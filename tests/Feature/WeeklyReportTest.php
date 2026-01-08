<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\DailyLog;
use App\Models\Weight;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class WeeklyReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_weekly_report(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->get('/reports/weekly');
        
        $response->assertStatus(200);
        $response->assertViewIs('reports.weekly');
    }

    public function test_weekly_report_shows_correct_data(): void
    {
        $user = User::factory()->create(['start_weight' => 108.0]);
        $weekStart = Carbon::today()->startOfWeek();
        
        // 建立本週的記錄
        DailyLog::factory()->create([
            'user_id' => $user->id,
            'date' => $weekStart,
            'weight' => 108.0,
            'daily_points' => 50,
        ]);
        
        DailyLog::factory()->create([
            'user_id' => $user->id,
            'date' => $weekStart->copy()->addDays(6),
            'weight' => 107.5,
            'daily_points' => 50,
        ]);
        
        $response = $this->actingAs($user)
            ->get('/reports/weekly');
        
        $response->assertStatus(200);
        $response->assertViewHas('week_start');
        $response->assertViewHas('week_end');
        $response->assertViewHas('start_weight');
        $response->assertViewHas('end_weight');
    }
}
