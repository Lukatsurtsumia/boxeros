<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Receives Paddle Billing webhooks and keeps each user's subscription state in sync.
 * We verify Paddle's HMAC signature, then update the matching user on subscription events.
 */
class PaddleWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        $secret = config('services.paddle.webhook_secret');

        if ($secret && ! $this->signatureIsValid($request, $secret)) {
            Log::warning('Paddle webhook: invalid signature');
            return response('Invalid signature', 403);
        }

        $event = (string) $request->input('event_type');
        $data  = (array) $request->input('data', []);

        // Only subscription lifecycle events change access.
        if (! str_starts_with($event, 'subscription.')) {
            return response('ignored', 200);
        }

        $user = $this->resolveUser($data);

        if (! $user) {
            Log::warning('Paddle webhook: no matching user', ['event' => $event, 'sub' => $data['id'] ?? null]);
            return response('no user', 200);
        }

        $endsAtRaw = data_get($data, 'current_billing_period.ends_at')
            ?? data_get($data, 'scheduled_change.effective_at');

        $user->forceFill([
            'paddle_subscription_id' => $data['id'] ?? $user->paddle_subscription_id,
            'paddle_status'          => $data['status'] ?? $user->paddle_status,
            'subscription_ends_at'   => $endsAtRaw ? Carbon::parse($endsAtRaw) : $user->subscription_ends_at,
        ])->save();

        return response('ok', 200);
    }

    /** Find the user this event belongs to — first by our custom_data, then by subscription id. */
    private function resolveUser(array $data): ?User
    {
        if ($id = data_get($data, 'custom_data.user_id')) {
            if ($user = User::find($id)) {
                return $user;
            }
        }

        if ($subId = ($data['id'] ?? null)) {
            return User::where('paddle_subscription_id', $subId)->first();
        }

        return null;
    }

    /** Verify Paddle's `Paddle-Signature: ts=…;h1=…` header via HMAC-SHA256 of "ts:rawBody". */
    private function signatureIsValid(Request $request, string $secret): bool
    {
        $parts = [];
        foreach (explode(';', (string) $request->header('Paddle-Signature')) as $segment) {
            [$k, $v] = array_pad(explode('=', $segment, 2), 2, '');
            $parts[$k] = $v;
        }

        $ts = $parts['ts'] ?? '';
        $h1 = $parts['h1'] ?? '';

        if ($ts === '' || $h1 === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $ts.':'.$request->getContent(), $secret);

        return hash_equals($expected, $h1);
    }
}
