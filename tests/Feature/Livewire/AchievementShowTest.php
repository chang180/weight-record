<?php

namespace Tests\Feature\Livewire;

use App\Livewire\AchievementShow;
use App\Models\User;
use App\Models\Achievement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AchievementShowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'AchievementSeeder']);
    }

    public function test_component_can_be_rendered(): void
    {
        $user = User::factory()->create();
        $achievement = Achievement::first();

        Livewire::actingAs($user)
            ->test(AchievementShow::class, ['achievement' => $achievement])
            ->assertSuccessful();
    }

    public function test_shows_achievement_details(): void
    {
        $user = User::factory()->create();
        $achievement = Achievement::first();

        Livewire::actingAs($user)
            ->test(AchievementShow::class, ['achievement' => $achievement])
            ->assertSee($achievement->name)
            ->assertSee($achievement->description);
    }

    public function test_shows_unlocked_status(): void
    {
        $user = User::factory()->create();
        $achievement = Achievement::first();
        
        $user->achievements()->attach($achievement->id, [
            'unlocked_at' => now(),
        ]);

        Livewire::actingAs($user)
            ->test(AchievementShow::class, ['achievement' => $achievement])
            ->assertSee('已解鎖')
            ->assertSet('isUnlocked', true);
    }

    public function test_shows_locked_status(): void
    {
        $user = User::factory()->create();
        $achievement = Achievement::first();

        Livewire::actingAs($user)
            ->test(AchievementShow::class, ['achievement' => $achievement])
            ->assertSee('未解鎖')
            ->assertSet('isUnlocked', false);
    }
}
