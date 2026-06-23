<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weight_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('weight_kg', 5, 2);
            $table->enum('context', ['morning', 'afternoon', 'night', 'pre_workout', 'post_workout', 'other'])->default('other');
            $table->dateTime('weighed_at');
            $table->timestamps();

            $table->index(['user_id', 'weighed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weight_entries');
    }
};
