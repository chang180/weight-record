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
            // 先將 user 欄位重新命名為 user_id
            $table->renameColumn('user', 'user_id');
        });

        // 然後在第二個操作中修改欄位類型並建立外鍵
        Schema::table('weights', function (Blueprint $table) {
            // 修改欄位類型為 unsignedBigInteger
            $table->unsignedBigInteger('user_id')->change();

            // 建立外鍵約束
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('weights', function (Blueprint $table) {
            // 移除外鍵約束
            $table->dropForeign(['user_id']);

            // 將欄位類型改回字串
            $table->string('user_id')->change();

            // 重新命名欄位
            $table->renameColumn('user_id', 'user');
        });
    }
};
