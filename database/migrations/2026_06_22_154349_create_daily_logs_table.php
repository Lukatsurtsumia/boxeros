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
            $table->date('log_date');
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->decimal('water_liters', 4, 2)->default(0);
            $table->integer('calories_consumed')->nullable();
            $table->integer('sleep_hours')->nullable();
            $table->integer('training_minutes')->nullable();
            $table->enum('mood', ['great', 'good', 'okay', 'tired', 'bad'])->default('good');
            $table->integer('energy_level')->default(5); // 1-10
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'log_date']);
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
