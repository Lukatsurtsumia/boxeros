{{--
    Reusable weekly card: day strip + headline metrics.
    Expects: $title (string), $subtitle (string), $days (Collection), $summary (array from
    Dashboard::summarizeWeek). Optional: $onPrev/$onNext (Livewire method names → renders ‹ › nav),
    $canNext (bool), $emptyText (string).
--}}
<div class="card h-full">

    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2 min-w-0">
            @isset($onPrev)
            <button type="button" wire:click="{{ $onPrev }}" title="{{ __('Previous week') }}" aria-label="{{ __('Previous week') }}"
                    style="display:flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:10px;background:rgba(255,255,255,0.05);border:1px solid var(--dark-border);color:var(--text-muted);font-size:1.2rem;line-height:1;cursor:pointer;flex-shrink:0;">‹</button>
            @endisset
            <div class="min-w-0">
                <div class="font-display text-lg font-bold">{{ $title }}</div>
                <div class="text-xs" style="color: var(--text-muted);">{{ $subtitle }}</div>
            </div>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
            @if($summary['daysLogged'] > 0)
            <div class="text-xs px-2 py-1 rounded-lg" style="background: rgba(255,255,255,0.05); color: var(--text-muted);">
                {{ $summary['daysLogged'] }}/7 {{ __('days') }}
            </div>
            @endif
            @isset($onNext)
            <button type="button" wire:click="{{ $onNext }}" title="{{ __('Next week') }}" aria-label="{{ __('Next week') }}"
                    @disabled(!($canNext ?? true))
                    style="display:flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:10px;background:rgba(255,255,255,0.05);border:1px solid var(--dark-border);color:var(--text-muted);font-size:1.2rem;line-height:1;cursor:pointer;flex-shrink:0; {{ ($canNext ?? true) ? '' : 'opacity:0.25;cursor:not-allowed;' }}">›</button>
            @endisset
        </div>
    </div>

    {{-- Day strip - always shown so you can see which week you're viewing --}}
    <div class="grid grid-cols-7 gap-1 mb-4">
        @foreach($days as $day)
        @php
            $hasLog    = $day['log'] !== null;
            $trained   = $day['trained'];
            $future    = $day['future'] ?? false;
            $isToday   = $day['today'] ?? false;
            $clickable = !$future && !empty($day['date']);
            $tag       = $clickable ? 'a' : 'div';
        @endphp
        <{{ $tag }}
             @if($clickable) href="{{ route('daily.log', ['date' => $day['date']]) }}" wire:navigate title="{{ __('View') }} {{ $day['label'] }} {{ $day['day_num'] }}" @endif
             class="rounded-xl py-2 px-0.5 text-center flex flex-col items-center gap-0.5"
             style="
                text-decoration: none; color: inherit;
                {{ $clickable ? 'cursor: pointer;' : '' }}
                background: {{ $trained ? 'rgba(192,57,43,0.14)' : ($hasLog ? 'rgba(255,255,255,0.03)' : 'transparent') }};
                border: 1px solid {{ $isToday ? 'rgba(243,156,18,0.5)' : ($trained ? 'rgba(192,57,43,0.3)' : 'rgba(255,255,255,0.06)') }};
                opacity: {{ $future ? '0.25' : ($hasLog ? '1' : '0.4') }};
             ">
            <div class="font-bold" style="font-size: 0.6rem; color: var(--text-muted); letter-spacing: 0.5px;">
                {{ strtoupper(substr($day['label'], 0, 1)) }}
            </div>
            <div class="font-display font-bold" style="font-size: 0.8rem; line-height: 1.2; {{ $isToday ? 'color: var(--gold);' : '' }}">
                {{ $day['day_num'] }}
            </div>
            <div style="font-size: 1rem; line-height: 1.4;">
                @if($future)
                    <span style="color: var(--text-muted);">·</span>
                @elseif($trained)
                    {{ $day['icon'] ?? '💪' }}
                @elseif($hasLog)
                    <span style="color: var(--text-muted); font-size: 0.7rem;">{{ __('rest') }}</span>
                @elseif($isToday)
                    <span style="color: var(--gold); font-size: 0.9rem; line-height: 1;" title="{{ __('today') }}">●</span>
                @else
                    <span style="color: var(--text-muted);">·</span>
                @endif
            </div>
            @if($day['weight'])
            <div style="font-size: 0.55rem; color: var(--text-muted);">{{ $day['weight'] }}</div>
            @endif
            @if($trained && $day['minutes'])
            <div style="font-size: 0.55rem; color: rgba(192,57,43,0.8);">
                {{ $day['minutes'] >= 60 ? round($day['minutes']/60, 1).'h' : $day['minutes'].'m' }}
            </div>
            @endif
        </{{ $tag }}>
        @endforeach
    </div>

    @if($summary['daysLogged'] > 0)
    {{-- Headline metrics --}}
    <div class="grid grid-cols-2 gap-2">

        <div class="stat-tile stat-tile-blood">
            <div class="flex items-end gap-1.5">
                <div class="font-display text-3xl font-bold leading-none" style="color: var(--blood);">{{ $summary['trainDays'] }}</div>
                <div class="text-sm font-semibold pb-0.5" style="color: var(--blood);">{{ __('sessions') }}</div>
            </div>
            <div class="text-xs mt-1" style="color: var(--text-muted);">
                {{ $summary['trainHrs'] > 0 ? $summary['trainHrs'] . ' ' . __('hrs training this week') : __('No training this week') }}
            </div>
        </div>

        <div class="stat-tile stat-tile-gold">
            <div class="font-display text-3xl font-bold leading-none" style="color: var(--gold);">
                {{ $summary['totalKcal'] > 0 ? number_format($summary['totalKcal']) : '-' }}
            </div>
            <div class="text-xs mt-1" style="color: var(--text-muted);">{{ __('kcal logged this week') }}</div>
        </div>

        <div class="stat-tile stat-tile-blue">
            <div class="flex items-end gap-1.5">
                <div class="font-display text-3xl font-bold leading-none" style="color: #3498db;">{{ $summary['avgWater'] ?? '-' }}</div>
                <div class="text-sm font-semibold pb-0.5" style="color: #3498db;">L</div>
            </div>
            <div class="text-xs mt-1" style="color: var(--text-muted);">{{ __('avg water / day') }}</div>
        </div>

        <div class="stat-tile">
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <div class="font-display text-xl font-bold leading-none">{{ $summary['avgSleep'] ?? '-' }}</div>
                    <div class="text-xs mt-1" style="color: var(--text-muted);">{{ __('avg sleep') }}</div>
                </div>
                <div>
                    <div class="font-display text-xl font-bold leading-none" style="color: #2ecc71;">{{ $summary['avgEnergy'] ?? '-' }}</div>
                    <div class="text-xs mt-1" style="color: var(--text-muted);">{{ __('avg energy') }}</div>
                </div>
            </div>
        </div>

    </div>
    @else
    <div class="text-center py-3 text-xs" style="color: var(--text-muted);">
        {{ $emptyText ?? __('No logs for this week yet') }}
    </div>
    @endif

</div>
