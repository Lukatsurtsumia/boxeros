<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around the Anthropic Messages API — the single place the app talks to Claude
 * (CORNER). Returns the reply text, or null when no key is set or the call fails, so callers
 * can fall back gracefully.
 */
class Corner
{
    /** Whether the AI coach is configured. */
    public static function enabled(): bool
    {
        return (bool) config('services.anthropic.key');
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
            $payload['system'] = $system;
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
