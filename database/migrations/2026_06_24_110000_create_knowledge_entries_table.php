<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_entries', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('content');               // the coaching material (markdown / plain text)
            $table->string('keywords')->nullable();    // optional comma-separated hints for retrieval
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_entries');
    }
};
