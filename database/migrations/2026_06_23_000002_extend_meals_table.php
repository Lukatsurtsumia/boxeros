<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meals', function (Blueprint $table) {
            $table->string('eaten_time', 5)->nullable()->after('eaten_at'); // HH:MM
            $table->enum('calories_source', ['unknown', 'ai_estimated', 'confirmed'])->default('unknown')->after('calories');
        });
    }

    public function down(): void
    {
        Schema::table('meals', function (Blueprint $table) {
            $table->dropColumn(['eaten_time', 'calories_source']);
        });
    }
};
