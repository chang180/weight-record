<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PointsDisplay extends Component
{
    public $availablePoints = 0;
    public $totalPoints = 0;

    public function mount(): void
    {
        $this->loadPoints();
    }

    protected function getListeners(): array
    {
        return [
            'points-updated' => 'handlePointsUpdated',
            'weight-recorded' => 'handleWeightRecorded',
        ];
    }

    public function handlePointsUpdated($points): void
    {
        $this->loadPoints();
    }

    public function handleWeightRecorded(): void
    {
        $this->loadPoints();
    }

    private function loadPoints(): void
    {
        $user = Auth::user();
        if ($user) {
            $this->availablePoints = $user->available_points;
            $this->totalPoints = $user->total_points;
        }
    }

    public function render()
    {
        return view('livewire.points-display');
    }
}
