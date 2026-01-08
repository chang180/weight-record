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
     * 安全扣除積分（確保積分不會低於 0）
     *
     * @param User $user 用戶物件
     * @param int $points 要扣除的積分
     * @return int 實際扣除的積分數量
     */
    public function deductPointsSafely(User $user, int $points): int
    {
        return DB::transaction(function () use ($user, $points) {
            // 重新載入用戶以取得最新積分
            $user->refresh();

            // 計算扣除後的積分（最低為 0）
            $newPoints = max(0, $user->available_points - $points);

            // 實際扣除量
            $actualDeducted = $user->available_points - $newPoints;

            // 更新可用積分
            $user->update(['available_points' => $newPoints]);

            return $actualDeducted;
        });
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
