<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

/**
 * Confirms a user's subscription by asking the Paddle API directly. Used right after
 * checkout so activation is instant and reliable — it does not depend on the webhook
 * arriving (the webhook stays as the source of truth for later renewals/cancellations).
 */
class PaddleSync
{
    public static function apiBase(): string
    {
        return config('services.paddle.env') === 'production'
            ? 'https://api.paddle.com'
            : 'https://sandbox-api.paddle.com';
    }

    /** Look up this user's live subscription and sync it. Returns true if access is now active. */
    public static function refresh(User $user): bool
    {
        $key = config('services.paddle.api_key');
        if (! $key) {
            return false;
        }

        $base = self::apiBase();

        try {
            $customerId = Http::withToken($key)->timeout(8)
                ->get("$base/customers", ['email' => $user->email])
                ->json('data.0.id');

            if (! $customerId) {
                return false;
            }

            $subs = Http::withToken($key)->timeout(8)
                ->get("$base/subscriptions", ['customer_id' => $customerId, 'per_page' => 10])
                ->json('data', []);

            $sub = collect($subs)->firstWhere('status', 'active')
                ?? collect($subs)->firstWhere('status', 'trialing')
                ?? collect($subs)->first();

            if (! $sub) {
                return false;
            }

            $endsAt = data_get($sub, 'current_billing_period.ends_at');

            $user->forceFill([
                'paddle_subscription_id' => $sub['id'] ?? $user->paddle_subscription_id,
                'paddle_status'          => $sub['status'] ?? $user->paddle_status,
                'subscription_ends_at'   => $endsAt ? Carbon::parse($endsAt) : $user->subscription_ends_at,
            ])->save();

            return $user->subscribedActive();
        } catch (\Throwable $e) {
            return false;
        }
    }
}
