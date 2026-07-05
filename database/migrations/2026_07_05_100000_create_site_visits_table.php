<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_visits', function (Blueprint $table) {
            $table->id();
            // Anonymous, non-reversible fingerprint (IP+UA+day+app key hashed) — privacy-friendly,
            // and the unique index means each visitor is counted at most once per day.
            $table->string('visitor_hash', 64);
            $table->date('visited_on');
            $table->timestamp('created_at')->nullable();

            $table->unique(['visitor_hash', 'visited_on']);
            $table->index('visited_on');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_visits');
    }
};
