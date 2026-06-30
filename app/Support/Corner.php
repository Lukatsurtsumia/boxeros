<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Thin wrapper around the Anthropic Messages API — the single place the app talks to Claude
 * (CORNER). Returns the reply text, or null when no key is set or the call fails, so callers
 * can fall back gracefully.
 */
class Corner
{
    /**
     * Per-user, per-day caps on AI calls. This protects the API budget — no single user can
     * drain the credits, and a runaway loop is bounded. Tune these as the app grows.
     */
    public const DAILY_LIMITS = [
        'chat'   => 20,   // CORNER coach messages (Sonnet 4.6 — best answers)
        'weekly' => 5,    // weekly recap regenerations (Sonnet)
        'meal'   => 40,   // meal calorie estimates (Haiku, cheap)
        'memory'  => 60,  // background long-term-memory updates (Haiku, cheap)
        'plan'    => 4,   // generate a structured training/nutrition plan (Sonnet)
    ];

    /** Whether the AI coach is configured. */
    public static function enabled(): bool
    {
        return (bool) config('services.anthropic.key');
    }

    /**
     * Consume one of today's allowance for a feature. Returns true if the call is allowed,
     * false if the current user has hit today's cap. Call this right before ask().
     */
    public static function allow(string $feature): bool
    {
        $max = self::DAILY_LIMITS[$feature] ?? 20;
        $key = self::limitKey($feature);

        if (RateLimiter::tooManyAttempts($key, $max)) {
            return false;
        }
        RateLimiter::hit($key, 86400); // resets after 24h
        return true;
    }

    /** How many calls of this feature the current user has left today. */
    public static function remaining(string $feature): int
    {
        return RateLimiter::remaining(self::limitKey($feature), self::DAILY_LIMITS[$feature] ?? 20);
    }

    private static function limitKey(string $feature): string
    {
        $userId = auth()->id() ?? 'guest';
        return "corner:{$feature}:{$userId}:" . now()->format('Ymd');
    }

    /**
     * Send messages to Claude. Returns the reply text, or null on no-key/failure.
     *
     * @param array        $messages  Anthropic-format messages (role + content).
     * @param string|null  $system    Optional system prompt.
     */
    public static function ask(
        array $messages,
        ?string $system = null,
        string $model = 'claude-sonnet-4-6',
        int $maxTokens = 1000
    ): ?string {
        $key = config('services.anthropic.key');
        if (!$key) {
            return null;
        }

        $payload = [
            'model'      => $model,
            'max_tokens' => $maxTokens,
            'messages'   => $messages,
        ];
        if ($system !== null) {
            // Send the system prompt as a cacheable block. The fighter's full profile + week
            // summary is large and identical across messages in a conversation, so prompt caching
            // bills it at full price once, then ~0.1x on every follow-up within the 5-min window.
            // (Prompts shorter than the cache minimum silently don't cache — no cost, no harm.)
            $payload['system'] = [[
                'type'          => 'text',
                'text'          => $system,
                'cache_control' => ['type' => 'ephemeral'],
            ]];
        }

        try {
            $response = Http::withHeaders([
                'x-api-key'         => $key,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->timeout(45)->post('https://api.anthropic.com/v1/messages', $payload);

            return $response->successful() ? $response->json('content.0.text') : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
