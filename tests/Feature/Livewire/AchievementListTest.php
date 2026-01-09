<?php

namespace Tests\Feature\Livewire;

use App\Livewire\AchievementList;
use App\Models\User;
use App\Models\Achievement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AchievementListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // 執行成就 seeder 以建立成就資料
        $this->artisan('db:seed', ['--class' => 'AchievementSeeder']);
    }

    public function test_component_can_be_rendered(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(AchievementList::class)
            ->assertSuccessful();
    }

    public function test_displays_achievements(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(AchievementList::class)
            ->assertSee('已解鎖成就')
            ->assertViewHas('achievements');
    }

    public function test_shows_unlocked_achievements_count(): void
    {
        $user = User::factory()->create();
        
        // 解鎖一個成就
        $achievement = Achievement::where('type', 'special')->first();
        $user->achievements()->attach($achievement->id, [
            'unlocked_at' => now(),
        ]);

        Livewire::actingAs($user)
            ->test(AchievementList::class)
            ->assertSee('1 /');
    }

    public function test_shows_achievement_groups(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(AchievementList::class)
            ->assertViewHas('achievementGroups');
    }

    public function test_displays_weight_milestones(): void
    {
        $user = User::factory()->create([
            'start_weight' => 100,
        ]);

        Livewire::actingAs($user)
            ->test(AchievementList::class)
            ->assertSee('體重里程碑');
    }

    public function test_displays_special_achievements(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(AchievementList::class)
            ->assertSee('特殊成就');
    }
}
