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
        Schema::create('injuries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('body_part');
            $table->string('title');
            $table->text('description');
            $table->enum('severity', ['minor', 'moderate', 'serious']);
            $table->enum('status', ['active', 'recovering', 'healed'])->default('active');
            $table->date('injury_date');
            $table->date('expected_recovery')->nullable();
            $table->text('ai_feedback')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('injuries');
    }
};
