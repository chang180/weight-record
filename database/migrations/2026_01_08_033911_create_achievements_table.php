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
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('成就代碼');
            $table->string('name')->comment('成就名稱');
            $table->text('description')->comment('成就描述');
            $table->string('icon')->comment('成就圖示(emoji)');
            $table->enum('type', ['weight_milestone', 'special', 'streak'])->comment('成就類型');
            $table->decimal('requirement_value', 4, 1)->nullable()->comment('需求值(如體重值)');
            $table->integer('points_reward')->default(0)->comment('獎勵積分');
            $table->integer('sort_order')->default(0)->comment('排序順序');
            $table->timestamps();

            $table->index('type');
            $table->index('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};
