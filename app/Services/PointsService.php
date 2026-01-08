<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class PointsService
{
    /**
     * 增加積分
     */
    public function addPoints(User $user, int $points, string $source = 'task'): void
    {
        DB::transaction(function () use ($user, $points) {
            $user->increment('total_points', $points);
            $user->increment('available_points', $points);
        });
    }

    /**
     * 扣除積分（兌換獎勵）
     */
    public function deductPoints(User $user, int $points): bool
    {
        if ($user->available_points < $points) {
            return false; // 積分不足
        }

        DB::transaction(function () use ($user, $points) {
            $user->decrement('available_points', $points);
        });

        return true;
    }

    /**
     * 取得可用積分
     */
    public function getAvailablePoints(User $user): int
    {
        return $user->available_points;
    }

    /**
     * 取得總積分
     */
    public function getTotalPoints(User $user): int
    {
        return $user->total_points;
    }
}
