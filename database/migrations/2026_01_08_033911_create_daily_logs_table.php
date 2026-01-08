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
        Schema::create('daily_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->decimal('weight', 4, 1)->nullable()->comment('當日體重');

            // 每日任務欄位
            $table->boolean('task_meal')->default(false)->comment('用餐任務完成');
            $table->boolean('task_walk')->default(false)->comment('散步任務完成');
            $table->boolean('task_no_snack')->default(false)->comment('不吃宵夜完成');
            $table->boolean('task_sleep')->default(false)->comment('早睡任務完成');
            $table->boolean('task_no_sugar')->default(false)->comment('不喝糖飲完成(假日)');

            // 積分欄位
            $table->integer('daily_points')->default(0)->comment('當日任務積分');
            $table->integer('weekly_points')->default(0)->comment('週任務積分');

            // 其他
            $table->text('notes')->nullable()->comment('備註');
            $table->timestamps();

            // 索引
            $table->unique(['user_id', 'date']);
            $table->index(['user_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_logs');
    }
};
