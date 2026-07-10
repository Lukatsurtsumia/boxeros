<div class="pb-6 space-y-3">
    @php
        $flags     = collect($insights)->whereIn('level', ['alert', 'warn'])->values();
        $topFlag   = $flags->first();
        $moodMap   = ['great'=>'🔥','good'=>'💪','okay'=>'😐','tired'=>'😴','bad'=>'🤕'];

        // Weight: trend + goal + sparkline
        $goal       = $profile?->goal_weight;
        $goalDelta  = ($currentWeight && $goal) ? round($currentWeight - $goal, 1) : null;
        $tr         = $weightTrend->values();
        $trendDelta = $tr->count() > 1 ? round($tr->last()['weight'] - $tr->first()['weight'], 1) : null;
        $spark = ''; $lastDot = null;
        if ($tr->count() > 1) {
            $ws = $tr->pluck('weight'); $lo = $ws->min(); $hi = $ws->max(); $span = max(0.1, $hi - $lo);
            $n = $tr->count(); $pad = 3; $pts = [];
            foreach ($tr as $i => $row) {
                $x = round($i / ($n - 1) * 100, 2);
                $y = round($pad + (1 - ($row['weight'] - $lo) / $span) * (32 - 2 * $pad), 2);
                $pts[] = "$x,$y";
            }
            $spark = implode(' ', $pts); $lastDot = end($pts);
        }

        // Today's plan adherence (compact)
        $pt = $planProgress['today'] ?? null;
        $ptSessions = $pt['sessions'] ?? [];
        $ptDone  = collect($ptSessions)->where('complete', true)->count();
        $ptTotal = count($ptSessions);

        $trainedToday = (int) ($todayLog->training_minutes ?? 0);
        $icons = ['boxing'=>'🥊','sparring'=>'🥊','gym'=>'🏋️','running'=>'🏃','cycling'=>'🚴','swimming'=>'🏊','yoga'=>'🧘','rest'=>'😌','other'=>'💪'];
    @endphp

    {{-- ════════════ 1 · FIGHTER + NEXT FIGHT ════════════ --}}
    <div class="card card-glow">
        <div class="flex items-center gap-3">
            @if($profile?->avatar)
                <img src="{{ Storage::url($profile->avatar) }}" class="w-14 h-14 rounded-2xl object-cover flex-shrink-0" style="border: 2px solid var(--blood);">
            @else
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-3xl flex-shrink-0" style="background: linear-gradient(145deg, var(--blood-dark), var(--blood));">🥊</div>
            @endif

            <div class="flex-1 min-w-0">
                <div class="font-display text-xl font-bold leading-none truncate">{{ auth()->user()->name }}</div>
                @if($profile)
                <div class="flex items-center gap-1.5 mt-1.5">
                    <span class="badge badge-gold">{{ $profile->wins }}W</span>
                    <span class="badge badge-red">{{ $profile->losses }}L</span>
                    <span class="badge badge-gray">{{ $profile->draws }}D</span>
                </div>
                @endif
            </div>

            <div class="flex-shrink-0">
                @if(!$profile)
                <a href="{{ route('boxer.profile') }}" wire:navigate class="btn-ghost text-xs px-2 py-1" style="text-decoration:none;">{{ __('Setup') }} →</a>
                @elseif($flags->count())
                <span class="badge" style="background: rgba(243,156,18,0.15); color: var(--gold);">{{ $flags->count() }} {{ __('to check') }}</span>
                @else
                <span class="text-lg font-bold" style="color: #2ecc71;" title="{{ __('On track') }}">✓</span>
                @endif
            </div>
        </div>

        @if($nextFight)
        @php
            $fSecs = (int) max(0, now()->diffInSeconds($nextFight->fight_date, false));
            $fDays = intdiv($fSecs, 86400);
            $fHrs  = intdiv($fSecs % 86400, 3600);
        @endphp
        <div class="flex items-center justify-between mt-3 pt-3" style="border-top: 1px solid var(--dark-border);">
            <div class="min-w-0">
                <div class="section-label" style="color: var(--gold);">{{ __('Next Fight') }}</div>
                <div class="font-display text-lg font-bold truncate mt-0.5">vs {{ $nextFight->opponent_name }}</div>
                <div class="text-xs" style="color: var(--text-muted);">{{ $nextFight->fight_date->translatedFormat('D, M j') }}@if($nextFight->location) · {{ $nextFight->location }}@endif</div>
            </div>
            <div class="text-right flex-shrink-0 ml-3">
                @if($fDays > 0 || $fHrs > 0)
                <div class="font-display text-3xl font-bold leading-none" style="color: var(--gold);">{{ $fDays }}<span class="text-base">d</span> {{ $fHrs }}<span class="text-base">h</span></div>
                <div class="text-xs mt-1" style="color: var(--text-muted);">{{ __('to go') }}</div>
                @else
                <div class="font-display text-lg font-bold" style="color: var(--gold);">{{ __('Fight time!') }}</div>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- ════════════ 2 · CORNER (top coaching flag only) ════════════ --}}
    @if($topFlag)
    @php $c = $topFlag['level'] === 'alert' ? '#ff6b6b' : 'var(--gold)'; @endphp
    <a href="{{ route('chat') }}" wire:navigate class="card flex items-start gap-3" style="text-decoration:none; border-left: 3px solid {{ $c }};">
        <span class="text-lg flex-shrink-0">{{ $topFlag['icon'] }}</span>
        <div class="min-w-0 flex-1">
            <div class="font-semibold text-sm" style="color: {{ $c }};">{{ $topFlag['title'] }}</div>
            <div class="text-xs mt-0.5 leading-relaxed" style="color: var(--text-muted);">{{ $topFlag['detail'] }}</div>
            <div class="text-xs mt-1.5" style="color: var(--gold);">{{ __('Ask CORNER about this') }} →</div>
        </div>
    </a>
    @endif

    {{-- ════════════ 3 · TODAY  +  WEIGHT (side by side on desktop) ════════════ --}}
    <div class="grid gap-3 lg:grid-cols-2 items-start">

        {{-- ── TODAY ── --}}
        <div class="card">
            <div class="flex items-center justify-between mb-3">
                <div class="section-label">{{ __('Today') }}</div>
                <div class="text-xs" style="color: var(--text-muted);">{{ now()->translatedFormat('l, M j') }}</div>
            </div>

            @if($todayLog || $trainedToday)
            <div class="grid grid-cols-3 gap-2 text-center">
                <div class="stat-tile">
                    <div class="font-display text-xl font-bold" style="color: var(--blood);">{{ $trainedToday ? $trainedToday.'m' : '-' }}</div>
                    <div class="text-xs mt-0.5" style="color: var(--text-muted);">{{ __('Training') }}</div>
                </div>
                <div class="stat-tile">
                    <div class="font-display text-xl font-bold" style="color: var(--gold);">{{ $todayCalories > 0 ? number_format($todayCalories) : '-' }}</div>
                    <div class="text-xs mt-0.5" style="color: var(--text-muted);">kcal</div>
                </div>
                <div class="stat-tile">
                    <div class="font-display text-xl font-bold" style="color: #3498db;">{{ number_format($waterToday, 1) }}</div>
                    <div class="text-xs mt-0.5" style="color: var(--text-muted);">L {{ __('water') }}</div>
                </div>
            </div>
            @if($todayLog)
            <div class="flex items-center justify-center gap-2 mt-2 text-xs" style="color: var(--text-muted);">
                <span class="text-base">{{ $moodMap[$todayLog->mood ?? 'good'] }}</span>
                <span>{{ __('Energy') }} {{ $todayLog->energy_level }}/10</span>
                @if($sweatLoss !== null)<span>· 💦 {{ $sweatLoss }} {{ __('kg sweat') }}</span>@endif
            </div>
            @endif
            @else
            <p class="text-sm mb-3" style="color: var(--text-muted);">{{ __("Nothing logged yet today.") }}</p>
            @endif

            {{-- Today's plan adherence (compact) --}}
            @if($ptTotal > 0)
            <div class="mt-3 pt-3" style="border-top: 1px solid var(--dark-border);">
                <div class="flex items-center justify-between text-xs mb-1.5">
                    <span style="color: var(--text-muted);">📋 {{ __("Today's plan") }}</span>
                    <span class="font-bold">{{ $ptDone }}/{{ $ptTotal }}</span>
                </div>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($ptSessions as $s)
                    <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-lg"
                          style="background: {{ $s['complete'] ? 'rgba(46,204,113,0.12)' : 'rgba(255,255,255,0.04)' }}; border: 1px solid {{ $s['complete'] ? 'rgba(46,204,113,0.25)' : 'var(--dark-border)' }};">
                        <span>{{ $icons[$s['type']] ?? '💪' }}</span>
                        <span style="color: {{ $s['complete'] ? '#2ecc71' : 'var(--text-muted)' }};">{{ $s['done'] }}/{{ $s['target'] }}m</span>
                    </span>
                    @endforeach
                </div>
            </div>
            @endif

            <a href="{{ route('daily.log') }}" wire:navigate class="btn-primary w-full mt-3 text-center block" style="text-decoration:none; padding-top:0.6rem; padding-bottom:0.6rem;">✍️ {{ __('Log today') }}</a>
        </div>

        {{-- ── WEIGHT ── --}}
        <div class="card" x-data="{ logging: {{ $currentWeight ? 'false' : 'true' }} }">
            <div class="flex items-center justify-between mb-3">
                <div class="section-label">⚖️ {{ __('Weight') }}</div>
                <button type="button" @click="logging = !logging"
                        class="text-xs px-2.5 py-1 rounded-lg"
                        style="border: 1px solid var(--dark-border); color: var(--gold); background: rgba(243,156,18,0.08);">
                    <span x-show="!logging">＋ {{ __('Weigh in') }}</span>
                    <span x-show="logging" x-cloak>✕ {{ __('Close') }}</span>
                </button>
            </div>

            @if($currentWeight)
            <div class="flex items-end justify-between gap-3">
                <div class="min-w-0">
                    <div class="flex items-end gap-1.5 leading-none">
                        <span class="font-display font-bold" style="font-size: 2.4rem;">{{ number_format($currentWeight, 1) }}</span>
                        <span class="text-base pb-1.5" style="color: var(--text-muted);">kg</span>
                    </div>
                    <div class="text-xs mt-1" style="color: var(--text-muted);">{{ $weightAgo ? __('last weighed :ago', ['ago' => $weightAgo]) : __('latest weigh-in') }}</div>
                </div>
                @if($trendDelta !== null && $trendDelta != 0)
                @php $down = $trendDelta < 0; @endphp
                <div class="flex items-center px-2.5 py-1 rounded-full flex-shrink-0"
                     style="background: {{ $down ? 'rgba(46,204,113,0.12)' : 'rgba(192,57,43,0.12)' }}; color: {{ $down ? '#2ecc71' : '#ff6b6b' }};">
                    <span class="text-sm font-bold">{{ $down ? '▼' : '▲' }} {{ abs($trendDelta) }} kg</span>
                </div>
                @endif
            </div>

            @if($spark)
            <svg viewBox="0 0 100 32" preserveAspectRatio="none" class="w-full mt-3" style="height: 36px; overflow: visible;">
                <polyline points="{{ $spark }}" fill="none" stroke="var(--gold)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" opacity="0.9"/>
                @if($lastDot)<circle cx="{{ explode(',', $lastDot)[0] }}" cy="{{ explode(',', $lastDot)[1] }}" r="2.5" fill="var(--gold)"/>@endif
            </svg>
            @endif

            @if($goalDelta !== null)
            <div class="mt-3 pt-3" style="border-top: 1px solid var(--dark-border);">
                <div class="flex items-center justify-between text-xs mb-1.5">
                    <span style="color: var(--text-muted);">🎯 {{ __('Goal') }} <strong style="color: var(--text-primary);">{{ rtrim(rtrim(number_format($goal, 1), '0'), '.') }} kg</strong></span>
                    @if($goalDelta == 0)
                    <span class="font-semibold" style="color: #2ecc71;">✓ {{ __('on weight') }}</span>
                    @else
                    <span class="font-semibold" style="color: var(--gold);">{{ abs($goalDelta) }} kg {{ $goalDelta > 0 ? __('to cut') : __('to gain') }}</span>
                    @endif
                </div>
                @php $prog = $goalDelta == 0 ? 100 : max(8, min(100, (int) round((1 - min(abs($goalDelta), 10) / 10) * 100))); @endphp
                <div class="progress-track">
                    <div class="progress-fill {{ $goalDelta == 0 ? 'progress-green' : 'progress-gold' }}" style="width: {{ $prog }}%;"></div>
                </div>
            </div>
            @endif
            @else
            <p class="text-sm" style="color: var(--text-muted);">{{ __('No weigh-in yet - log your first one to start tracking.') }}</p>
            @endif

            {{-- Today's weigh-ins (compact chips) --}}
            @if($todayWeighIns->count() > 0)
            <div class="flex flex-wrap gap-1.5 mt-3">
                @foreach($todayWeighIns as $w)
                <span wire:key="weighin-{{ $w->id }}" class="inline-flex items-center gap-1.5 text-xs px-2 py-1 rounded-lg" style="background: rgba(255,255,255,0.04); border: 1px solid var(--dark-border);">
                    <span style="color: var(--text-muted);">{{ $w->weighed_at->format('g:i A') }}</span>
                    <strong>{{ number_format($w->weight_kg, 1) }}</strong>
                    <button type="button" wire:click="deleteWeighIn({{ $w->id }})" wire:confirm="{{ __('Delete this weigh-in?') }}" style="color: var(--text-muted); line-height: 1;">✕</button>
                </span>
                @endforeach
            </div>
            @endif

            {{-- Quick-log (hidden by default) --}}
            <div x-show="logging" x-cloak x-collapse class="mt-3 pt-3" style="border-top: 1px solid var(--dark-border);">
                <form wire:submit="saveWeighIn" class="flex gap-2">
                    <input type="number" step="0.1" inputmode="decimal" min="30" max="300" wire:model="weighInValue" class="input-dark" placeholder="{{ __('e.g. 74.2 kg') }}" style="flex: 1;">
                    <button type="submit" class="btn-primary px-5">
                        <span wire:loading.remove wire:target="saveWeighIn">{{ __('Save') }}</span>
                        <span wire:loading wire:target="saveWeighIn">…</span>
                    </button>
                </form>
                @error('weighInValue') <div class="text-xs mt-1" style="color: var(--blood);">{{ $message }}</div> @enderror
                <div class="flex gap-1.5 mt-2 flex-wrap">
                    @foreach(['morning'=>'🌅 '.__('Morning'),'afternoon'=>'☀️ '.__('Afternoon'),'night'=>'🌙 '.__('Night'),'pre_workout'=>'🥊 '.__('Pre'),'post_workout'=>'💦 '.__('Post')] as $val => $label)
                    <button type="button" wire:click="$set('weighInContext', '{{ $val }}')" class="text-xs px-2.5 py-1 rounded-lg transition"
                            style="border: 1px solid {{ $weighInContext === $val ? 'var(--blood)' : 'var(--dark-border)' }};
                                   background: {{ $weighInContext === $val ? 'rgba(192,57,43,0.15)' : 'transparent' }};
                                   color: {{ $weighInContext === $val ? '#ff6b6b' : 'var(--text-muted)' }};">{{ $label }}</button>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

    {{-- ════════════ 4 · THIS WEEK ════════════ --}}
    @include('livewire.partials.week-card', [
        'title'     => $weekTitle,
        'subtitle'  => $weekRange,
        'days'      => $weekDays,
        'summary'   => $weekSummary,
        'onPrev'    => 'prevWeek',
        'onNext'    => 'nextWeek',
        'canNext'   => !$isCurrentWeek,
        'emptyText' => __('Nothing logged this week'),
    ])

    {{-- ════════════ 5 · CORNER WEEK RECAP (subtle) ════════════ --}}
    @if($weeklyConclusion)
    <div class="card">
        <div class="flex items-center justify-between mb-2">
            <div class="section-label">🧠 {{ __("CORNER's week recap") }}</div>
            <button type="button" wire:click="generateConclusion" class="text-xs" style="color: var(--gold);">
                <span wire:loading.remove wire:target="generateConclusion">↻</span>
                <span wire:loading wire:target="generateConclusion">…</span>
            </button>
        </div>
        <div class="md-content text-sm" style="color: rgba(255,255,255,0.88);">{!! \Illuminate\Support\Str::markdown($weeklyConclusion, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}</div>
    </div>
    @elseif($recapLocked)
    <div class="card" style="text-align:center;">
        <div class="text-sm">🔒 {{ __('Your first weekly recap unlocks after a full week') }} - <strong style="color: var(--gold);">{{ trans_choice('{1}:count day to go|[2,*]:count days to go', $firstWeekDaysLeft, ['count' => $firstWeekDaysLeft]) }}</strong></div>
        <div class="text-xs mt-1" style="color: var(--text-muted);">{{ __('Keep logging - CORNER reviews your week once it has the full picture.') }}</div>
    </div>
    @else
    <button type="button" wire:click="generateConclusion" class="btn-ghost w-full py-2.5 text-sm">
        <span wire:loading.remove wire:target="generateConclusion">🧠 {{ __("Get CORNER's recap of this week") }}</span>
        <span wire:loading wire:target="generateConclusion">{{ __('CORNER is reviewing…') }}</span>
    </button>
    @endif

    <style>
        .md-content > *:first-child { margin-top: 0; }
        .md-content > *:last-child { margin-bottom: 0; }
        .md-content p { margin: 0 0 0.5rem; line-height: 1.55; }
        .md-content strong { font-weight: 700; color: #fff; }
        .md-content ul, .md-content ol { margin: 0.3rem 0 0.5rem; padding-left: 1.15rem; }
        .md-content li { margin: 0.25rem 0; line-height: 1.5; }
        .md-content li::marker { color: var(--blood); }
        .md-content h1, .md-content h2, .md-content h3 { font-weight: 700; font-size: 0.95rem; color: var(--gold); margin: 0.6rem 0 0.35rem; }
        .md-content blockquote { border-left: 3px solid var(--blood); margin: 0.4rem 0; padding-left: 0.7rem; color: var(--text-muted); }
        .md-content hr { border: none; border-top: 1px solid var(--dark-border); margin: 0.6rem 0; }
    </style>

</div>
