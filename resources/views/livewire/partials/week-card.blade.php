{{--
    Reusable weekly card: day strip + headline metrics.
    Expects: $title (string), $subtitle (string), $days (Collection), $summary (array from
    Dashboard::summarizeWeek). Optional: $accent ('blood'|'gold', defaults 'blood').
--}}
@php $accent = $accent ?? 'blood'; @endphp
<div class="card h-full">

    <div class="flex items-center justify-between mb-4">
        <div>
            <div class="font-display text-lg font-bold">{{ $title }}</div>
            <div class="text-xs" style="color: var(--text-muted);">{{ $subtitle }}</div>
        </div>
        @if($summary['daysLogged'] > 0)
        <div class="text-xs px-2 py-1 rounded-lg" style="background: rgba(255,255,255,0.05); color: var(--text-muted);">
            {{ $summary['daysLogged'] }}/7 days
        </div>
        @endif
    </div>

    @if($summary['daysLogged'] > 0)

    {{-- Day strip --}}
    <div class="grid grid-cols-7 gap-1 mb-4">
        @foreach($days as $day)
        @php
            $hasLog  = $day['log'] !== null;
            $trained = $day['trained'];
            $future  = $day['future'] ?? false;
        @endphp
        <div class="rounded-xl py-2 px-0.5 text-center flex flex-col items-center gap-0.5"
             style="
                background: {{ $trained ? 'rgba(192,57,43,0.14)' : ($hasLog ? 'rgba(255,255,255,0.03)' : 'transparent') }};
                border: 1px solid {{ ($day['today'] ?? false) ? 'rgba(243,156,18,0.5)' : ($trained ? 'rgba(192,57,43,0.3)' : 'rgba(255,255,255,0.06)') }};
                opacity: {{ $future ? '0.25' : ($hasLog ? '1' : '0.4') }};
             ">
            <div class="font-bold" style="font-size: 0.6rem; color: var(--text-muted); letter-spacing: 0.5px;">
                {{ strtoupper(substr($day['label'], 0, 1)) }}
            </div>
            <div class="font-display font-bold" style="font-size: 0.8rem; line-height: 1.2; {{ ($day['today'] ?? false) ? 'color: var(--gold);' : '' }}">
                {{ $day['day_num'] }}
            </div>
            <div style="font-size: 1rem; line-height: 1.4;">
                @if($future)
                    <span style="color: var(--text-muted);">·</span>
                @elseif($trained)
                    {{ $day['icon'] ?? '💪' }}
                @elseif($hasLog)
                    <span style="color: var(--text-muted); font-size: 0.7rem;">rest</span>
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
        </div>
        @endforeach
    </div>

    {{-- Headline metrics --}}
    <div class="grid grid-cols-2 gap-2">

        <div class="stat-tile stat-tile-blood">
            <div class="flex items-end gap-1.5">
                <div class="font-display text-3xl font-bold leading-none" style="color: var(--blood);">{{ $summary['trainDays'] }}</div>
                <div class="text-sm font-semibold pb-0.5" style="color: var(--blood);">sessions</div>
            </div>
            <div class="text-xs mt-1" style="color: var(--text-muted);">
                {{ $summary['trainHrs'] > 0 ? $summary['trainHrs'] . ' hrs total training' : 'No training logged' }}
            </div>
        </div>

        <div class="stat-tile stat-tile-gold">
            <div class="font-display text-3xl font-bold leading-none" style="color: var(--gold);">
                {{ $summary['totalKcal'] > 0 ? number_format($summary['totalKcal']) : '—' }}
            </div>
            <div class="text-xs mt-1" style="color: var(--text-muted);">total kcal logged</div>
        </div>

        <div class="stat-tile stat-tile-blue">
            <div class="flex items-end gap-1.5">
                <div class="font-display text-3xl font-bold leading-none" style="color: #3498db;">{{ $summary['avgWater'] ?? '—' }}</div>
                <div class="text-sm font-semibold pb-0.5" style="color: #3498db;">L</div>
            </div>
            <div class="text-xs mt-1" style="color: var(--text-muted);">avg water / day</div>
        </div>

        <div class="stat-tile">
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <div class="font-display text-xl font-bold leading-none">{{ $summary['avgSleep'] ?? '—' }}</div>
                    <div class="text-xs mt-1" style="color: var(--text-muted);">avg sleep</div>
                </div>
                <div>
                    <div class="font-display text-xl font-bold leading-none" style="color: #2ecc71;">{{ $summary['avgEnergy'] ?? '—' }}</div>
                    <div class="text-xs mt-1" style="color: var(--text-muted);">avg energy</div>
                </div>
            </div>
        </div>

    </div>

    @else
    <div class="text-center py-6" style="color: var(--text-muted);">
        <div class="text-3xl mb-2">📅</div>
        <div class="text-sm">{{ $emptyText ?? 'No logs for this week yet' }}</div>
        <div class="text-xs mt-1">Log daily to build your recap here</div>
    </div>
    @endif

</div>
