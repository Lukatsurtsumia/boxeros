<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->boolean('is_active')->default(false);
            $table->json('schedule')->nullable();              // { mon:[{type,minutes}], ... sun:[...] }
            $table->unsignedSmallInteger('target_calories')->nullable();
            $table->decimal('target_water', 4, 1)->nullable(); // liters / day
            $table->decimal('target_sleep', 4, 1)->nullable(); // hours / night
            $table->decimal('target_weight', 5, 2)->nullable();// kg
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
