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
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('start_weight', 4, 1)->nullable()->comment('起始體重(kg)');
            $table->integer('total_points')->default(0)->comment('總積分');
            $table->integer('available_points')->default(0)->comment('可用積分');
            $table->integer('current_streak')->default(0)->comment('當前連續達成天數');
            $table->integer('longest_streak')->default(0)->comment('最長連續達成天數');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'start_weight',
                'total_points',
                'available_points',
                'current_streak',
                'longest_streak',
            ]);
        });
    }
};
