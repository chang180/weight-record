<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('weights', function (Blueprint $table) {
            // 添加複合索引優化用戶查詢
            $table->index(['user_id', 'record_at']);

            // 添加單獨索引優化日期篩選
            $table->index('record_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('weights', function (Blueprint $table) {
            // 移除索引
            $table->dropIndex(['user_id', 'record_at']);
            $table->dropIndex(['record_at']);
        });
    }
};
