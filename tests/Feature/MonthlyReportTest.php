<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\DailyLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class MonthlyReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_monthly_report(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->get('/reports/monthly');
        
        $response->assertStatus(200);
        $response->assertViewIs('reports.monthly');
    }

    public function test_monthly_report_shows_correct_data(): void
    {
        $user = User::factory()->create(['start_weight' => 108.0]);
        $monthStart = Carbon::today()->startOfMonth();
        
        // 建立本月的記錄
        DailyLog::factory()->create([
            'user_id' => $user->id,
            'date' => $monthStart,
            'weight' => 108.0,
            'daily_points' => 50,
        ]);
        
        $response = $this->actingAs($user)
            ->get('/reports/monthly');
        
        $response->assertStatus(200);
        $response->assertViewHas('month_start');
        $response->assertViewHas('month_end');
        $response->assertViewHas('tasks_completed');
        $response->assertViewHas('points_earned');
    }
}
