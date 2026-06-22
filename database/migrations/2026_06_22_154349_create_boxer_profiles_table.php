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
        Schema::create('boxer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nickname')->nullable();
            $table->string('weight_class')->nullable();
            $table->decimal('current_weight', 5, 2)->nullable();
            $table->decimal('height_cm', 5, 1)->nullable();
            $table->integer('experience_years')->default(0);
            $table->integer('total_fights')->default(0);
            $table->integer('wins')->default(0);
            $table->integer('losses')->default(0);
            $table->integer('draws')->default(0);
            $table->string('gym')->nullable();
            $table->string('trainer')->nullable();
            $table->string('stance')->default('orthodox');
            $table->decimal('reach_cm', 5, 1)->nullable();
            $table->text('bio')->nullable();
            $table->string('avatar')->nullable();
            $table->decimal('goal_weight', 5, 2)->nullable();
            $table->decimal('daily_water_goal_liters', 3, 1)->default(3.0);
            $table->integer('daily_calorie_goal')->default(2500);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boxer_profiles');
    }
};
