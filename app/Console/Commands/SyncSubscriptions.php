<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\PaddleSync;
use Illuminate\Console\Command;

/**
 * Nightly safety net for billing: re-checks every known subscriber against the Paddle
 * API and updates their status. Catches cancellations, renewals, and failed payments
 * even when webhooks don't arrive (e.g. blocked at the CDN edge).
 */
class SyncSubscriptions extends Command
{
    protected $signature = 'boxeros:sync-subscriptions';

    protected $description = 'Refresh every subscriber\'s status from the Paddle API (cancellations, renewals)';

    public function handle(): int
    {
        $users = User::whereNotNull('paddle_subscription_id')
            ->orWhereNotNull('paddle_status')
            ->get();

        $this->info("Syncing {$users->count()} subscriber(s) with Paddle…");

        foreach ($users as $user) {
            $before = $user->paddle_status;
            PaddleSync::refresh($user);
            $after = $user->fresh()->paddle_status;

            $this->line("  #{$user->id} {$user->email}: ".($before ?: 'none').' → '.($after ?: 'none'));
        }

        $this->info('Done.');

        return self::SUCCESS;
    }
}
