<div class="pb-6">

    {{-- ═══ FIGHTER IDENTITY ═══ --}}
    <div class="card card-glow mb-3">
        <div class="flex items-center gap-4">
            @if($profile?->avatar)
                <img src="{{ Storage::url($profile->avatar) }}"
                     class="w-[72px] h-[72px] rounded-2xl object-cover flex-shrink-0"
                     style="border: 2px solid var(--blood);">
            @else
                <div class="w-[72px] h-[72px] rounded-2xl flex items-center justify-center text-4xl flex-shrink-0"
                     style="background: linear-gradient(145deg, var(--blood-dark), var(--blood));">🥊</div>
            @endif

            <div class="flex-1 min-w-0">
                <div class="font-display text-2xl font-bold leading-none">
                    {{ auth()->user()->name }}
                </div>
                @if($profile?->nickname)
                <div class="text-sm mt-0.5" style="color: var(--gold);">"{{ $profile->nickname }}"</div>
                @endif
                <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                    @if($profile)
                    <span class="badge badge-gold">{{ $profile->wins }}W</span>
                    <span class="badge badge-red">{{ $profile->losses }}L</span>
                    <span class="badge badge-gray">{{ $profile->draws }}D</span>
                    @endif
                    @if($profile?->weight_class)
                    <span class="text-xs" style="color: var(--text-muted);">· {{ $profile->weight_class }}</span>
                    @endif
                </div>
                @if($profile?->current_weight)
                <div class="text-xs mt-1.5" style="color: var(--text-muted);">
                    {{ $profile->current_weight }} kg
                    @if($profile->goal_weight)
                    <span style="color: rgba(255,255,255,0.2);">→</span>
                    <span style="color: var(--gold);">{{ $profile->goal_weight }} kg goal</span>
                    @endif
                    @if($profile->height_cm) · {{ $profile->height_cm }} cm @endif
                </div>
                @endif
            </div>

            <div class="flex flex-col items-end gap-2 flex-shrink-0">
                @if($activeInjuries > 0)
                <a href="{{ route('injuries') }}" style="text-decoration:none;">
                    <span class="badge badge-red">⚠ {{ $activeInjuries }} inj.</span>
                </a>
                @endif
                @if(!$profile)
                <a href="{{ route('boxer.profile') }}" class="btn-ghost text-xs px-2 py-1" style="text-decoration:none;">Setup →</a>
                @endif
            </div>
        </div>
    </div>

    {{-- ═══ NEXT FIGHT + TODAY ═══ --}}
    <div class="grid gap-3 {{ $nextFight ? 'lg:grid-cols-2' : '' }} mb-3">

        @if($nextFight)
        @php $daysLeft = max(0, (int) now()->diffInDays($nextFight->fight_date, false)); @endphp
        <div class="card card-gold" style="border-color: rgba(243,156,18,0.35);">
            <div class="flex items-start justify-between h-full">
                <div>
                    <div class="section-label mb-1" style="color: var(--gold);">Next Fight</div>
                    <div class="font-display text-2xl font-bold">vs {{ $nextFight->opponent_name }}</div>
                    @if($nextFight->event_name)
                    <div class="text-sm mt-0.5" style="color: var(--text-muted);">{{ $nextFight->event_name }}</div>
                    @endif
                    @if($nextFight->location)
                    <div class="text-xs mt-0.5" style="color: var(--text-muted);">📍 {{ $nextFight->location }}</div>
                    @endif
                </div>
                <div class="text-right flex-shrink-0 ml-3">
                    <div class="font-display text-4xl font-bold leading-none" style="color: var(--gold);">{{ $daysLeft }}</div>
                    <div class="text-xs" style="color: var(--text-muted);">days out</div>
                    <div class="text-xs mt-1" style="color: var(--text-muted);">{{ $nextFight->fight_date->format('M j, Y') }}</div>
                </div>
            </div>
        </div>
        @endif

        {{-- Today --}}
        @if($todayLog)
        <div class="card">
            <div class="flex items-center justify-between mb-3">
                <div class="section-label">Today</div>
                <div class="text-xs" style="color: var(--text-muted);">{{ now()->format('l, M j') }}</div>
            </div>
            <div class="grid grid-cols-4 gap-2 text-center">
                <div>
                    <div class="font-display text-2xl font-bold">{{ $currentWeight ? number_format($currentWeight, 1) : '—' }}</div>
                    <div class="text-xs mt-0.5" style="color: var(--text-muted);">kg now</div>
                </div>
                <div>
                    <div class="font-display text-2xl font-bold" style="color: #3498db;">{{ number_format($waterToday, 1) }}</div>
                    <div class="text-xs mt-0.5" style="color: var(--text-muted);">L water</div>
                </div>
                <div>
                    <div class="font-display text-2xl font-bold" style="color: var(--gold);">
                        {{ $todayCalories > 0 ? number_format($todayCalories) : '—' }}
                    </div>
                    <div class="text-xs mt-0.5" style="color: var(--text-muted);">kcal</div>
                </div>
                <div>
                    <div class="text-2xl">{{ ['great'=>'🔥','good'=>'💪','okay'=>'😐','tired'=>'😴','bad'=>'🤕'][$todayLog->mood ?? 'good'] }}</div>
                    <div class="text-xs mt-0.5" style="color: var(--text-muted);">{{ $todayLog->energy_level }}/10</div>
                </div>
            </div>
            @if($todayLog->training_minutes)
            <div class="flex items-center gap-2 mt-3 pt-3 text-xs" style="border-top: 1px solid var(--dark-border); color: var(--text-muted);">
                <span style="color: var(--blood);">●</span>
                <span>{{ $todayLog->training_minutes }} min
                    @if($todayLog->training_type) {{ $todayLog->training_type }} @endif
                </span>
                @if($sweatLoss !== null)
                <span>·</span>
                <span style="color: #3498db;">{{ $sweatLoss }} kg sweat</span>
                @endif
            </div>
            @endif
        </div>
        @else
        <a href="{{ route('daily.log') }}" class="card flex items-center gap-3 py-4"
           style="text-decoration: none; border-style: dashed; border-color: rgba(192,57,43,0.4);">
            <span class="text-2xl">📋</span>
            <div>
                <div class="text-sm font-semibold" style="color: var(--blood);">Log today's stats</div>
                <div class="text-xs" style="color: var(--text-muted);">Weight, training, water, sleep & mood</div>
            </div>
            <span class="ml-auto text-sm" style="color: var(--blood);">→</span>
        </a>
        @endif

    </div>

    {{-- ═══ WEIGH-IN ═══ --}}
    <div class="grid gap-3 lg:grid-cols-2 mb-3 items-start">
        @include('livewire.partials.weigh-in', ['compact' => true])

        <div class="card">
            <div class="flex items-center justify-between mb-2">
                <div class="section-label">Weight Today</div>
                @if($currentWeight)
                <span class="text-xs" style="color: var(--text-muted);">
                    {{ number_format($currentWeight, 1) }} kg now{{ $weightAgo ? ' · ' . $weightAgo : '' }}
                </span>
                @endif
            </div>
            @if($todayWeighIns->count() > 0)
            <div class="space-y-1.5">
                @foreach($todayWeighIns as $w)
                <div wire:key="weighin-{{ $w->id }}" class="flex items-center justify-between text-sm">
                    <span style="color: var(--text-muted);">{{ $w->weighed_at->format('g:i A') }} · {{ $w->context_label }}</span>
                    <div class="flex items-center gap-3">
                        <span class="font-display font-bold">{{ number_format($w->weight_kg, 1) }} kg</span>
                        <button type="button" wire:click="deleteWeighIn({{ $w->id }})" wire:confirm="Delete this weigh-in?"
                                class="text-xs" style="color: var(--text-muted);" title="Delete weigh-in">✕</button>
                    </div>
                </div>
                @endforeach
            </div>
            @if($sweatLoss !== null)
            <div class="mt-2 pt-2 text-xs" style="border-top: 1px solid var(--dark-border); color: var(--text-muted);">
                💦 Sweat loss <strong style="color: #3498db;">{{ $sweatLoss }} kg</strong> (pre → post workout)
            </div>
            @endif
            @else
            <p class="text-sm" style="color: var(--text-muted);">No weigh-in yet today — log one above whenever you step on the scale.</p>
            @endif
        </div>
    </div>

    {{-- ═══ THIS WEEK + LAST WEEK ═══ --}}
    <div class="grid gap-3 lg:grid-cols-2 mb-3 items-stretch">
        @include('livewire.partials.week-card', [
            'title'     => 'This Week',
            'subtitle'  => $thisWeekStart->format('M j') . ' – today',
            'days'      => $thisWeekDays,
            'summary'   => $tw,
            'emptyText' => 'Nothing logged this week yet',
        ])
        @include('livewire.partials.week-card', [
            'title'     => 'Last Week',
            'subtitle'  => $lastWeekStart->format('M j') . ' – ' . $lastWeekEnd->format('M j, Y'),
            'days'      => $lastWeekDays,
            'summary'   => $lw,
            'emptyText' => 'No logs from last week',
        ])
    </div>

    {{-- ═══ WEEKLY CONCLUSION ═══ --}}
    <div class="card mb-3">
        <div class="flex items-center justify-between mb-2">
            <div class="section-label">🧠 Weekly Conclusion</div>
            @if($weeklyConclusion)
            <button type="button" wire:click="generateConclusion" class="text-xs" style="color: var(--gold);">
                <span wire:loading.remove wire:target="generateConclusion">↻ Regenerate</span>
                <span wire:loading wire:target="generateConclusion">…</span>
            </button>
            @endif
        </div>
        @if($weeklyConclusion)
        <p class="text-sm leading-relaxed" style="color: rgba(255,255,255,0.85); white-space: pre-line;">{{ $weeklyConclusion }}</p>
        @else
        <p class="text-xs mb-3" style="color: var(--text-muted);">CORNER reviews your training, weight, sleep and discipline and tells you how the week really went.</p>
        <button type="button" wire:click="generateConclusion" class="btn-primary w-full py-2.5">
            <span wire:loading.remove wire:target="generateConclusion">🧠 Get CORNER's take on this week</span>
            <span wire:loading wire:target="generateConclusion">CORNER is reviewing your week…</span>
        </button>
        @endif
    </div>

    {{-- ═══ WEIGHT TREND ═══ --}}
    @if($weightTrend->count() > 1)
    <div class="card mb-3">
        <div class="flex items-center justify-between mb-3">
            <div class="section-label">Weight Trend <span style="text-transform: none; color: var(--text-muted);">· morning</span></div>
            @php
                $firstW = $weightTrend->first()['weight'];
                $lastW  = $weightTrend->last()['weight'];
                $delta  = round($lastW - $firstW, 1);
            @endphp
            <div class="text-xs font-semibold" style="color: {{ $delta <= 0 ? '#2ecc71' : 'var(--blood)' }};">
                {{ $delta > 0 ? '+' : '' }}{{ $delta }} kg
            </div>
        </div>
        @php
            $wVals = $weightTrend->pluck('weight');
            $maxW  = $wVals->max();
            $minW  = $wVals->min();
            $range = max(0.5, $maxW - $minW);
        @endphp
        <div class="flex items-end gap-1" style="height: 72px;">
            @foreach($weightTrend as $wp)
            <div class="flex-1 flex flex-col items-center gap-1">
                <div class="w-full rounded-t-sm"
                     style="background: linear-gradient(180deg, var(--blood), var(--blood-dark));
                            height: {{ max(6, (($wp['weight'] - $minW) / $range) * 60 + 6) }}px;
                            opacity: 0.75;"></div>
                <div style="color: var(--text-muted); font-size: 0.55rem; white-space: nowrap;">
                    {{ $wp['date']->format('M j') }}
                </div>
            </div>
            @endforeach
        </div>
        <div class="flex justify-between mt-2 text-xs" style="color: var(--text-muted);">
            <span>{{ $minW }} kg</span>
            <span>{{ $maxW }} kg</span>
        </div>
    </div>
    @endif

    {{-- ═══ QUICK ACTIONS ═══ --}}
    <div class="grid grid-cols-3 gap-2">
        <a href="{{ route('meals') }}" class="card text-center py-4" style="text-decoration: none;">
            <div class="text-2xl mb-1.5">🍽️</div>
            <div class="text-xs font-semibold" style="color: var(--text-muted);">Meals</div>
        </a>
        <a href="{{ route('injuries') }}" class="card text-center py-4" style="text-decoration: none;">
            <div class="text-2xl mb-1.5">🩹</div>
            <div class="text-xs font-semibold" style="color: var(--text-muted);">Injuries</div>
        </a>
        <a href="{{ route('chat') }}" class="card text-center py-4" style="text-decoration: none;">
            <div class="text-2xl mb-1.5">🤖</div>
            <div class="text-xs font-semibold" style="color: var(--text-muted);">CORNER</div>
        </a>
    </div>

</div>
