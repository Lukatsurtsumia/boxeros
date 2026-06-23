{{--
    Quick weigh-in widget. Requires the host component to use the LogsWeight trait
    (props: weighInValue, weighInContext; action: saveWeighIn).
    Optional: $compact (bool) for the tighter dashboard variant.
--}}
@php
    $chips = [
        'morning'      => '🌅 Morning',
        'afternoon'    => '☀️ Afternoon',
        'night'        => '🌙 Night',
        'pre_workout'  => '🥊 Pre',
        'post_workout' => '💦 Post',
    ];
@endphp
<div class="card">
    <div class="section-label mb-2">⚖️ Log Weigh-in</div>
    <form wire:submit="saveWeighIn" class="flex gap-2">
        <input type="number" step="0.1" inputmode="decimal" wire:model="weighInValue"
               class="input-dark" placeholder="e.g. 74.2 kg" style="flex: 1;">
        <button type="submit" class="btn-primary px-5">
            <span wire:loading.remove wire:target="saveWeighIn">Log</span>
            <span wire:loading wire:target="saveWeighIn">…</span>
        </button>
    </form>
    @error('weighInValue') <div class="text-xs mt-1" style="color: var(--blood);">{{ $message }}</div> @enderror
    <div class="flex gap-1.5 mt-2 flex-wrap">
        @foreach($chips as $val => $label)
        <button type="button" wire:click="$set('weighInContext', '{{ $val }}')"
                class="text-xs px-2.5 py-1 rounded-lg transition"
                style="border: 1px solid {{ $weighInContext === $val ? 'var(--blood)' : 'var(--dark-border)' }};
                       background: {{ $weighInContext === $val ? 'rgba(192,57,43,0.15)' : 'transparent' }};
                       color: {{ $weighInContext === $val ? '#ff6b6b' : 'var(--text-muted)' }};">
            {{ $label }}
        </button>
        @endforeach
    </div>
    @unless($compact ?? false)
    <p class="text-xs mt-2" style="color: var(--text-muted);">Log anytime you weigh in — no pressure if you don't know it.</p>
    @endunless
</div>
