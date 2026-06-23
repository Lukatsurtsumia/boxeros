<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_logs', function (Blueprint $table) {
            $table->decimal('weight_before_kg', 5, 2)->nullable()->after('weight_kg');
            $table->decimal('weight_after_kg', 5, 2)->nullable()->after('weight_before_kg');
            $table->string('training_type', 50)->nullable()->after('training_minutes');
            $table->unsignedTinyInteger('soda_cans')->default(0)->after('water_liters');
            $table->unsignedTinyInteger('alcohol_units')->default(0)->after('soda_cans');
        });
    }

    public function down(): void
    {
        Schema::table('daily_logs', function (Blueprint $table) {
            $table->dropColumn(['weight_before_kg', 'weight_after_kg', 'training_type', 'soda_cans', 'alcohol_units']);
        });
    }
};
