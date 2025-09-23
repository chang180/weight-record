<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Weight extends Model
{
    use HasFactory;

    /**
     * 可批量賦值的屬性
     */
    protected $fillable = [
        'user_id',
        'weight',
        'record_at',
        'note'
    ];

    /**
     * 屬性轉換
     */
    protected function casts(): array
    {
        return [
            'record_at' => 'date',
            'weight' => 'decimal:1',
        ];
    }

    /**
     * 獲取擁有此體重記錄的用戶
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
