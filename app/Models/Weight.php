<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Weight extends Model
{
    use HasFactory;

    /**
     * 可批量賦值的屬性
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'weight',
        'record_at',
        'note'
    ];

    /**
     * 屬性的轉換類型
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'record_at' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime'
        ];
    }

    /**
     * 獲取擁有此體重記錄的用戶
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
