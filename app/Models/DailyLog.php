<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'weight',
        'task_meal',
        'task_walk',
        'task_no_snack',
        'task_sleep',
        'task_no_sugar',
        'daily_points',
        'weekly_points',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'weight' => 'decimal:1',
            'task_meal' => 'boolean',
            'task_walk' => 'boolean',
            'task_no_snack' => 'boolean',
            'task_sleep' => 'boolean',
            'task_no_sugar' => 'boolean',
            'daily_points' => 'integer',
            'weekly_points' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 檢查是否完成所有任務
     */
    public function isAllTasksCompleted(): bool
    {
        $isWeekend = in_array($this->date->dayOfWeek, [0, 6]); // 0=週日, 6=週六

        if ($isWeekend) {
            return $this->task_meal && $this->task_walk &&
                $this->task_no_snack && $this->task_no_sugar;
        } else {
            return $this->task_meal && $this->task_walk &&
                $this->task_no_snack && $this->task_sleep;
        }
    }
}
