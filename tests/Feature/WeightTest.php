<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Weight;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeightTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_weight_records(): void
    {
        $user = User::factory()->create();
        Weight::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/record');

        $response->assertStatus(200);
        $response->assertViewIs('record');
        $response->assertViewHas('weights');
    }

    public function test_user_can_create_weight_record(): void
    {
        $user = User::factory()->create();

        $weightData = [
            'weight' => 70.5,
            'record_at' => now()->format('Y-m-d'),
            'note' => 'Test weight record',
        ];

        $response = $this->actingAs($user)
            ->post('/weights', $weightData);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('weights', [
            'user_id' => $user->id,
            'weight' => 70.5,
            'note' => 'Test weight record',
        ]);
    }

    public function test_weight_validation_works(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/weights', [
                'weight' => 'invalid',
                'record_at' => 'invalid-date',
            ]);

        $response->assertSessionHasErrors(['weight', 'record_at']);
    }

    public function test_user_can_view_chart(): void
    {
        $user = User::factory()->create();
        Weight::factory()->count(5)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/chart');

        $response->assertStatus(200);
        $response->assertViewIs('chart');
        $response->assertViewHas('weights');
    }
}
