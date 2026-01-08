<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\PointsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PointsServiceTest extends TestCase
{
    use RefreshDatabase;

    private PointsService $pointsService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pointsService = new PointsService();
    }

    public function test_add_points_increases_total_and_available(): void
    {
        $user = User::factory()->create([
            'total_points' => 100,
            'available_points' => 50,
        ]);

        $this->pointsService->addPoints($user, 20, 'test');

        $user->refresh();
        $this->assertEquals(120, $user->total_points);
        $this->assertEquals(70, $user->available_points);
    }

    public function test_deduct_points_successfully(): void
    {
        $user = User::factory()->create([
            'available_points' => 100,
        ]);

        $result = $this->pointsService->deductPoints($user, 30);

        $this->assertTrue($result);
        $user->refresh();
        $this->assertEquals(70, $user->available_points);
    }

    public function test_deduct_points_fails_when_insufficient(): void
    {
        $user = User::factory()->create([
            'available_points' => 20,
        ]);

        $result = $this->pointsService->deductPoints($user, 50);

        $this->assertFalse($result);
        $user->refresh();
        $this->assertEquals(20, $user->available_points);
    }

    public function test_deduct_points_safely_reduces_points(): void
    {
        $user = User::factory()->create([
            'available_points' => 100,
        ]);

        $deducted = $this->pointsService->deductPointsSafely($user, 30);

        $this->assertEquals(30, $deducted);
        $user->refresh();
        $this->assertEquals(70, $user->available_points);
    }

    public function test_deduct_points_safely_prevents_negative(): void
    {
        $user = User::factory()->create([
            'available_points' => 20,
        ]);

        $deducted = $this->pointsService->deductPointsSafely($user, 50);

        $this->assertEquals(20, $deducted); // 只扣除實際擁有的
        $user->refresh();
        $this->assertEquals(0, $user->available_points); // 不會變成負數
    }

    public function test_deduct_points_safely_with_zero_points(): void
    {
        $user = User::factory()->create([
            'available_points' => 0,
        ]);

        $deducted = $this->pointsService->deductPointsSafely($user, 10);

        $this->assertEquals(0, $deducted);
        $user->refresh();
        $this->assertEquals(0, $user->available_points);
    }
}
