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
        Schema::create('fights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('opponent_name');
            $table->string('event_name')->nullable();
            $table->string('venue')->nullable();
            $table->string('location')->nullable();
            $table->dateTime('fight_date');
            $table->string('weight_class')->nullable();
            $table->integer('rounds')->default(3);
            $table->enum('result', ['win', 'loss', 'draw', 'no_contest', 'upcoming'])->default('upcoming');
            $table->string('result_method')->nullable(); // KO, TKO, UD, SD, etc.
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fights');
    }
};
