<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('trial_ends_at')->nullable()->after('is_admin');
            $table->string('paddle_subscription_id')->nullable()->after('trial_ends_at');
            $table->string('paddle_status')->nullable()->after('paddle_subscription_id');
            $table->timestamp('subscription_ends_at')->nullable()->after('paddle_status');
        });

        // Give every existing account a fresh 7-day trial from the moment payments ship,
        // so nobody who signed up before monetization is suddenly locked out.
        DB::table('users')->whereNull('trial_ends_at')->update([
            'trial_ends_at' => now()->addDays(7),
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'trial_ends_at',
                'paddle_subscription_id',
                'paddle_status',
                'subscription_ends_at',
            ]);
        });
    }
};
