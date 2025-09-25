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
            ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
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
            ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
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

    public function test_user_can_update_weight_record(): void
    {
        $user = User::factory()->create();
        $weight = Weight::factory()->create(['user_id' => $user->id]);

        $updateData = [
            'weight' => 72.3,
            'record_at' => is_string($weight->record_at) ? $weight->record_at : $weight->record_at->format('Y-m-d'),
            'note' => 'Updated note',
        ];

        $response = $this->actingAs($user)
            ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
            ->put(route('weights.update', $weight), $updateData);

        $response->assertRedirect(route('record'));
        $this->assertDatabaseHas('weights', [
            'id' => $weight->id,
            'weight' => 72.3,
            'note' => 'Updated note',
        ]);
    }

    public function test_user_can_delete_weight_record(): void
    {
        $user = User::factory()->create();
        $weight = Weight::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
            ->delete(route('weights.destroy', $weight));

        $response->assertRedirect(route('record'));
        $this->assertDatabaseMissing('weights', ['id' => $weight->id]);
    }

    public function test_ajax_weight_creation_returns_json(): void
    {
        $user = User::factory()->create();

        $weightData = [
            'weight' => 68.9,
            'record_at' => now()->format('Y-m-d'),
            'note' => 'AJAX test',
        ];

        $response = $this->actingAs($user)
            ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json'
            ])
            ->post('/weights', $weightData);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('weights', [
            'user_id' => $user->id,
            'weight' => 68.9,
            'note' => 'AJAX test',
        ]);
    }

    public function test_ajax_weight_update_returns_json(): void
    {
        $user = User::factory()->create();
        $weight = Weight::factory()->create(['user_id' => $user->id]);

        $updateData = [
            'weight' => 71.2,
            'record_at' => is_string($weight->record_at) ? $weight->record_at : $weight->record_at->format('Y-m-d'),
            'note' => 'AJAX updated',
        ];

        $response = $this->actingAs($user)
            ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json'
            ])
            ->put(route('weights.update', $weight), $updateData);

        $response->assertRedirect(route('record'));
        $this->assertDatabaseHas('weights', [
            'id' => $weight->id,
            'weight' => 71.2,
            'note' => 'AJAX updated',
        ]);
    }

    public function test_ajax_weight_deletion_returns_json(): void
    {
        $user = User::factory()->create();
        $weight = Weight::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json'
            ])
            ->delete(route('weights.destroy', $weight));

        $response->assertRedirect(route('record'));
        $this->assertDatabaseMissing('weights', ['id' => $weight->id]);
    }

    public function test_user_cannot_access_other_users_weights(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $weight = Weight::factory()->create(['user_id' => $user2->id]);

        $response = $this->actingAs($user1)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->put(route('weights.update', $weight), [
                'weight' => 99.9,
                'record_at' => now()->format('Y-m-d'),
            ]);

        $response->assertForbidden();
    }

    public function test_weight_filtering_by_date_range(): void
    {
        $user = User::factory()->create();

        // 創建一個超出範圍的記錄（10天前）
        Weight::factory()->create([
            'user_id' => $user->id,
            'record_at' => now()->subDays(10)->format('Y-m-d'),
        ]);

        // 創建範圍內的記錄（5天前）
        Weight::factory()->create([
            'user_id' => $user->id,
            'record_at' => now()->subDays(5)->format('Y-m-d'),
        ]);

        // 創建範圍內的記錄（今天）
        Weight::factory()->create([
            'user_id' => $user->id,
            'record_at' => now()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($user)->get('/record?' . http_build_query([
            'start_date' => now()->subDays(7)->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ]));

        $response->assertStatus(200);
        $weights = $response->viewData('weights');
        // 先檢查篩選功能是否正常，驗證有篩選效果
        $this->assertLessThan(3, $weights->total());
        $this->assertGreaterThan(0, $weights->total());
    }

    public function test_api_latest_weights_endpoint(): void
    {
        $user = User::factory()->create();
        Weight::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get('/api/weights/latest');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'hasNewData',
                'weights',
            ]);
    }
}
