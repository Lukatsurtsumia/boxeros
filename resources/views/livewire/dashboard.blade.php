<div class="space-y-4 pb-4">

    {{-- Fighter header --}}
    <div class="card card-glow">
        <div class="flex items-center gap-4">
            @if($profile?->avatar)
                <img src="{{ Storage::url($profile->avatar) }}" class="w-16 h-16 rounded-2xl object-cover" style="border: 2px solid var(--blood);">
            @else
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-3xl" style="background: linear-gradient(135deg, var(--blood-dark), var(--blood));">
                    🥊
                </div>
            @endif
            <div class="flex-1 min-w-0">
                <div class="font-display text-2xl font-bold leading-tight truncate">
                    {{ $profile?->nickname ? '"' . $profile->nickname . '"' : auth()->user()->name }}
                </div>
                <div class="text-sm mt-0.5" style="color: var(--text-muted);">
                    {{ $profile?->weight_class ?? 'Boxer' }}
                    @if($profile) · {{ $profile->record }} @endif
                </div>
                @if($profile)
                <div class="flex gap-3 mt-2 text-xs" style="color: var(--text-muted);">
                    <span>{{ $profile->current_weight ?? '--' }} kg</span>
                    <span>·</span>
                    <span>{{ $profile->height_cm ?? '--' }} cm</span>
                    <span>·</span>
                    <span>{{ $profile->experience_years }}y exp</span>
                </div>
                @endif
            </div>
            @if($activeInjuries > 0)
            <div class="flex flex-col items-center">
                <span class="badge badge-red">{{ $activeInjuries }} injury</span>
            </div>
            @endif
        </div>
    </div>

    {{-- Next fight countdown --}}
    @if($nextFight)
    <div class="card card-gold">
        <div class="flex items-center justify-between mb-1">
            <span class="text-xs font-semibold uppercase tracking-wider" style="color: var(--gold);">Next Fight</span>
            <span class="badge badge-gold">{{ $nextFight->fight_date->diffForHumans() }}</span>
        </div>
        <div class="font-display text-xl font-bold">vs {{ $nextFight->opponent_name }}</div>
        @if($nextFight->event_name)
        <div class="text-sm mt-0.5" style="color: var(--text-muted);">{{ $nextFight->event_name }}</div>
        @endif
        <div class="mt-3">
            @php $daysLeft = max(0, now()->diffInDays($nextFight->fight_date, false)); @endphp
            <div class="flex justify-between text-xs mb-1.5" style="color: var(--text-muted);">
                <span>Camp progress</span><span>{{ $daysLeft }} days left</span>
            </div>
            <div class="progress-track">
                <div class="progress-fill progress-gold" style="width: {{ min(100, max(5, 100 - ($daysLeft / 90 * 100))) }}%"></div>
            </div>
        </div>
    </div>
    @endif

    {{-- Today stats row --}}
    <div class="grid grid-cols-2 gap-3">
        {{-- Water --}}
        <div class="card">
            <div class="text-xs font-semibold uppercase tracking-wider mb-2" style="color: #3498db;">Hydration</div>
            <div class="stat-num" style="color: #3498db;">{{ number_format($waterToday, 1) }}<span class="text-sm font-normal ml-1">L</span></div>
            <div class="text-xs mb-2" style="color: var(--text-muted);">/ {{ $waterGoal }} L goal</div>
            <div class="progress-track">
                <div class="progress-fill progress-blue" style="width: {{ $waterPct }}%"></div>
            </div>
            <div class="text-xs mt-1.5 text-right" style="color: var(--text-muted);">{{ $waterPct }}%</div>
        </div>

        {{-- Calories --}}
        <div class="card">
            <div class="text-xs font-semibold uppercase tracking-wider mb-2" style="color: var(--gold);">Calories</div>
            <div class="stat-num" style="color: var(--gold);">{{ number_format($todayCalories) }}</div>
            <div class="text-xs mb-2" style="color: var(--text-muted);">/ {{ number_format($calorieGoal) }} goal</div>
            <div class="progress-track">
                <div class="progress-fill progress-gold" style="width: {{ $caloriePct }}%"></div>
            </div>
            <div class="text-xs mt-1.5 text-right" style="color: var(--text-muted);">{{ $caloriePct }}%</div>
        </div>
    </div>

    {{-- Today's mood & energy --}}
    @if($todayLog)
    <div class="card">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold uppercase tracking-wider mb-1" style="color: var(--text-muted);">Today's Status</div>
                <div class="flex items-center gap-3">
                    <span class="text-2xl">
                        @php $moodEmoji = ['great'=>'🔥','good'=>'💪','okay'=>'😐','tired'=>'😴','bad'=>'🤕'][$todayLog->mood ?? 'good']; @endphp
                        {{ $moodEmoji }}
                    </span>
                    <div>
                        <div class="text-sm font-medium capitalize">{{ $todayLog->mood }}</div>
                        <div class="text-xs" style="color: var(--text-muted);">Energy {{ $todayLog->energy_level }}/10</div>
                    </div>
                </div>
            </div>
            <div class="text-right">
                @if($todayLog->weight_kg)
                <div class="stat-num text-3xl">{{ $todayLog->weight_kg }}<span class="text-sm font-normal">kg</span></div>
                @endif
                @if($todayLog->training_minutes)
                <div class="text-xs mt-1" style="color: var(--text-muted);">{{ $todayLog->training_minutes }} min training</div>
                @endif
            </div>
        </div>
    </div>
    @else
    <a href="{{ route('daily.log') }}" class="card block text-center py-4" style="border-style: dashed; border-color: var(--blood); color: var(--blood);">
        <span class="text-2xl block mb-1">📋</span>
        <span class="text-sm font-semibold">Log today's stats</span>
    </a>
    @endif

    {{-- Weight trend (last 7 days) --}}
    @if($recentLogs->count() > 1)
    <div class="card">
        <div class="text-xs font-semibold uppercase tracking-wider mb-3" style="color: var(--text-muted);">Weight Trend (7 days)</div>
        <div class="flex items-end justify-between gap-1" style="height: 60px;">
            @php
                $weights = $recentLogs->pluck('weight_kg')->filter()->reverse()->values();
                $maxW = $weights->max() ?: 1;
                $minW = $weights->min() ?: 0;
                $range = max(1, $maxW - $minW);
            @endphp
            @foreach($recentLogs->reverse() as $log)
            @if($log->weight_kg)
            <div class="flex-1 flex flex-col items-center gap-1">
                <div class="w-full rounded-sm" style="background: var(--blood); height: {{ max(10, (($log->weight_kg - $minW) / $range) * 50 + 10) }}px; opacity: 0.8;"></div>
                <div class="text-xs" style="color: var(--text-muted); font-size: 0.6rem;">{{ $log->log_date->format('D') }}</div>
            </div>
            @endif
            @endforeach
        </div>
    </div>
    @endif

    {{-- Quick links --}}
    <div class="grid grid-cols-4 gap-2">
        <a href="{{ route('boxer.profile') }}" class="card text-center py-3 hover:card-glow" style="text-decoration: none;">
            <div class="text-xl mb-1">👤</div>
            <div class="text-xs" style="color: var(--text-muted);">Profile</div>
        </a>
        <a href="{{ route('meals') }}" class="card text-center py-3" style="text-decoration: none;">
            <div class="text-xl mb-1">🍽️</div>
            <div class="text-xs" style="color: var(--text-muted);">Meals</div>
        </a>
        <a href="{{ route('chat') }}" class="card text-center py-3" style="text-decoration: none;">
            <div class="text-xl mb-1">🤖</div>
            <div class="text-xs" style="color: var(--text-muted);">Coach</div>
        </a>
        <a href="{{ route('photos') }}" class="card text-center py-3" style="text-decoration: none;">
            <div class="text-xl mb-1">📸</div>
            <div class="text-xs" style="color: var(--text-muted);">Gallery</div>
        </a>
    </div>

</div>
