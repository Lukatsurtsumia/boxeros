<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coach_memories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->longText('content')->nullable(); // CORNER's durable memory of this fighter (markdown)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coach_memories');
    }
};
