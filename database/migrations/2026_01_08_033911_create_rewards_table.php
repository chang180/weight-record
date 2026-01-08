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
        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('reward_type')->comment('獎勵類型');
            $table->string('reward_name')->comment('獎勵名稱');
            $table->integer('points_spent')->comment('花費積分');
            $table->timestamp('redeemed_at')->comment('兌換時間');
            $table->text('notes')->nullable()->comment('備註');
            $table->timestamps();

            $table->index(['user_id', 'redeemed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rewards');
    }
};
