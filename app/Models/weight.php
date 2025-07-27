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
        'user',
        'weight',
        'record_at',
        'note'
    ];

    /**
     * 應該被轉換為日期的屬性
     *
     * @var array
     */
    protected $dates = [
        'record_at',
        'created_at',
        'updated_at'
    ];

    /**
     * 獲取擁有此體重記錄的用戶
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user', 'id');
    }
}
