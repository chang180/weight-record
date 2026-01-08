<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_profile_edit_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertStatus(200);
        $response->assertViewIs('profile.edit');
    }

    public function test_user_can_update_profile_with_start_weight(): void
    {
        $user = User::factory()->create(['start_weight' => null]);

        $response = $this->actingAs($user)
            ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
            ->put('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'height' => $user->height,
                'start_weight' => 75.5,
            ]);

        $response->assertRedirect('/profile');
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'start_weight' => 75.5,
        ]);
    }

    public function test_start_weight_validation_min(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
            ->put('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'start_weight' => 25, // 低於最小值 30
            ]);

        $response->assertSessionHasErrors(['start_weight']);
    }

    public function test_start_weight_validation_max(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
            ->put('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'start_weight' => 250, // 超過最大值 200
            ]);

        $response->assertSessionHasErrors(['start_weight']);
    }

    public function test_start_weight_must_be_numeric(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
            ->put('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'start_weight' => 'not-a-number',
            ]);

        $response->assertSessionHasErrors(['start_weight']);
    }

    public function test_start_weight_can_be_null(): void
    {
        $user = User::factory()->create(['start_weight' => 80.0]);

        $response = $this->actingAs($user)
            ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
            ->put('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'start_weight' => null,
            ]);

        $response->assertRedirect('/profile');
        
        $user->refresh();
        $this->assertNull($user->start_weight);
    }
}
