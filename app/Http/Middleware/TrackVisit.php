<?php

namespace App\Http\Middleware;

use App\Models\SiteVisit;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Records one row per unique visitor per day so the admin panel can show visitor counts.
 * Deduped by a per-session flag (fast path) and a unique DB index (safety). Skips bots,
 * assets, AJAX/Livewire, and never lets an analytics hiccup break a page.
 */
class TrackVisit
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            if ($this->shouldCount($request)) {
                $today = now()->toDateString();
                $flag = 'visit_'.$today;

                if (! $request->session()->has($flag)) {
                    $hash = hash('sha256', $request->ip().'|'.$request->userAgent().'|'.$today.'|'.config('app.key'));
                    SiteVisit::firstOrCreate(
                        ['visitor_hash' => $hash, 'visited_on' => $today],
                        ['created_at' => now()],
                    );
                    $request->session()->put($flag, true);
                }
            }
        } catch (Throwable $e) {
            // Analytics must never break a page load.
        }

        return $response;
    }

    private function shouldCount(Request $request): bool
    {
        if (! $request->isMethod('GET') || $request->ajax() || $request->wantsJson()) {
            return false;
        }
        if ($request->hasHeader('X-Livewire') || ! $request->hasSession()) {
            return false;
        }

        // Only count real people — skip crawlers/bots so numbers aren't inflated.
        $ua = strtolower((string) $request->userAgent());
        if ($ua === '' || preg_match('/bot|crawl|spider|slurp|bingpreview|google|yandex|duckduck|facebookexternalhit|monitor|curl|wget|python|go-http|headless|lighthouse/i', $ua)) {
            return false;
        }

        return true;
    }
}
