<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A piece of the coach's own material (training methodology, weight-cut protocols, nutrition guides…)
 * that CORNER treats as authoritative reference. The active entries are injected into CORNER's prompt
 * so its advice is grounded in YOUR content, not just general knowledge.
 */
class KnowledgeEntry extends Model
{
    protected $fillable = ['title', 'content', 'keywords', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    /**
     * Build the reference block injected into CORNER's system prompt from the active entries.
     * Capped so the prompt stays a sensible size (and cache-friendly); for a large library this
     * is where keyword/vector retrieval would slot in later.
     */
    public static function promptBlock(int $maxEntries = 12, int $maxChars = 8000): ?string
    {
        $entries = static::query()->where('is_active', true)
            ->orderBy('id')->take($maxEntries)->get();

        if ($entries->isEmpty()) {
            return null;
        }

        $block = "COACH'S REFERENCE MATERIAL (the gym's own methodology — treat this as authoritative and "
            . "prefer it over generic knowledge whenever it's relevant to the question):\n";

        foreach ($entries as $e) {
            $block .= "\n### {$e->title}\n{$e->content}\n";
            if (strlen($block) > $maxChars) {
                $block = substr($block, 0, $maxChars) . "\n…(reference truncated)\n";
                break;
            }
        }

        return $block;
    }
}
