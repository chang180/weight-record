<?php

namespace App\Livewire;

use App\Models\Achievement;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AchievementShow extends Component
{
    public Achievement $achievement;
    public $isUnlocked = false;
    public $userAchievement = null;

    public function mount($achievement): void
    {
        // 支援傳入 Achievement 模型或 ID
        if (is_numeric($achievement)) {
            $this->achievement = Achievement::findOrFail($achievement);
        } else {
            $this->achievement = $achievement;
        }
        $this->loadAchievementStatus();
    }

    private function loadAchievementStatus(): void
    {
        $user = Auth::user();
        $this->isUnlocked = $this->achievement->isUnlockedBy($user);

        if ($this->isUnlocked) {
            $this->userAchievement = $user->achievements()
                ->where('achievements.id', $this->achievement->id)
                ->first()?->pivot;
        }
    }

    public function render()
    {
        return view('livewire.achievement-show');
    }
}
