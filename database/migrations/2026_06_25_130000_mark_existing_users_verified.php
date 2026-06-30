<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Email verification is being switched on. Grandfather in every existing user as verified so
     * nobody who signed up before this is suddenly locked out — new sign-ups must verify.
     */
    public function up(): void
    {
        DB::table('users')->whereNull('email_verified_at')->update(['email_verified_at' => now()]);
    }

    public function down(): void
    {
        // No-op — we don't un-verify users.
    }
};
