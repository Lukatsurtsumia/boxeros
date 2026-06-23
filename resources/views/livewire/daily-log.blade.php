<div class="pb-6 space-y-4">

    @php
        $sodaSugar  = $soda_cans * 39;
        $sodaKcal   = $soda_cans * 140;
        $emptyKcal  = $sodaKcal + $alcoholKcal;
        $inCamp     = $fightDays !== null && $fightDays <= 42;
        $latestToday = $todayWeighIns->first();
        $moodMap    = ['great'=>'🔥','good'=>'💪','okay'=>'😐','tired'=>'😴','bad'=>'🤕'];
        $trainIcons = ['boxing'=>'🥊','sparring'=>'🥊','gym'=>'🏋️','running'=>'🏃','cycling'=>'🚴','swimming'=>'🏊','yoga'=>'🧘','other'=>'💪'];
        $ctxColor   = ['morning'=>'var(--gold)','afternoon'=>'#3498db','night'=>'#9b8cff','pre_workout'=>'#ff6b6b','post_workout'=>'#2ecc71','other'=>'var(--text-muted)'];
        $isToday    = $log_date === now()->toDateString();
    @endphp

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <div class="font-display text-2xl font-bold">Daily Log</div>
            <div class="text-xs" style="color: var(--text-muted);">{{ now()->format('l, F j') }}</div>
        </div>
        @if($fightDays !== null)
        <a href="{{ route('fights') }}" class="badge badge-gold" style="text-decoration: none;">🥊 {{ $fightDays }}d to fight</a>
        @endif
    </div>

    {{-- ═══════════ RESULTS (today only) ═══════════ --}}
    @if($isToday)
    <div class="grid gap-4 lg:grid-cols-2 items-start">

        {{-- Weight --}}
        <div class="card">
            <div class="flex items-end justify-between mb-3">
                <div class="section-label">Weight</div>
                @if($latestToday)
                <div class="text-right leading-none">
                    <span class="font-display text-3xl font-bold">{{ number_format($latestToday->weight_kg, 1) }}</span>
                    <span class="text-sm" style="color: var(--text-muted);">kg</span>
                </div>
                @endif
            </div>
            @if($todayWeighIns->count() > 0)
            <div class="space-y-0">
                @foreach($todayWeighIns as $w)
                <div wire:key="weighin-{{ $w->id }}" class="flex items-center justify-between py-2" style="border-top: 1px solid var(--dark-border);">
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs" style="color: var(--text-muted); min-width: 58px;">{{ $w->weighed_at->format('g:i A') }}</span>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full"
                              style="background: rgba(255,255,255,0.05); color: {{ $ctxColor[$w->context] ?? 'var(--text-muted)' }};">{{ $w->context_label }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="font-display font-bold">{{ number_format($w->weight_kg, 1) }} <span class="text-xs" style="color: var(--text-muted);">kg</span></span>
                        <button type="button" wire:click="deleteWeighIn({{ $w->id }})" wire:confirm="Delete this weigh-in?"
                                class="text-xs" style="color: var(--text-muted);" title="Delete weigh-in">✕</button>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="flex items-center justify-between mt-2 pt-2" style="border-top: 1px solid var(--dark-border);">
                @if($sweatLoss !== null)
                <span class="text-xs" style="color: var(--text-muted);">💦 Sweat loss <strong style="color: #3498db;">{{ $sweatLoss }} kg</strong></span>
                @else
                <span></span>
                @endif
                <button type="button" wire:click="clearTodayWeighIns" wire:confirm="Delete ALL of today's weigh-ins?"
                        class="text-xs" style="color: var(--text-muted);">Clear all</button>
            </div>
            @else
            <p class="text-sm" style="color: var(--text-muted);">No weigh-in logged yet today.</p>
            @endif
        </div>

        {{-- Today at a glance --}}
        <div class="card">
            <div class="section-label mb-3">Today at a Glance</div>
            <div class="grid grid-cols-3 gap-3 text-center">
                <div>
                    <div class="font-display text-xl font-bold" style="color: #3498db;">{{ number_format($water_liters, 1) }}</div>
                    <div class="text-xs" style="color: var(--text-muted);">L water</div>
                </div>
                <div>
                    <div class="font-display text-xl font-bold" style="color: var(--gold);">{{ $training_minutes ? $training_minutes.'m' : '—' }}</div>
                    <div class="text-xs truncate" style="color: var(--text-muted);">{{ count($sessions) > 1 ? count($sessions).' sessions' : ($training_type ?: 'rest') }}</div>
                </div>
                <div>
                    <div class="font-display text-xl font-bold">{{ $sleep_hours ?? '—' }}</div>
                    <div class="text-xs" style="color: var(--text-muted);">sleep h</div>
                </div>
                <div>
                    <div class="text-2xl">{{ $moodMap[$mood] ?? '💪' }}</div>
                    <div class="text-xs" style="color: var(--text-muted);">mood</div>
                </div>
                <div>
                    <div class="font-display text-xl font-bold" style="color: var(--blood);">{{ $energy_level }}</div>
                    <div class="text-xs" style="color: var(--text-muted);">energy</div>
                </div>
                <div>
                    <div class="font-display text-xl font-bold" style="color: #2ecc71;">{{ $confirmedCalories ? number_format($confirmedCalories) : '—' }}</div>
                    <div class="text-xs" style="color: var(--text-muted);">kcal</div>
                </div>
            </div>
        </div>
    </div>
    @else
    {{-- Editing a past day --}}
    <div class="card" style="border-color: rgba(243,156,18,0.4);">
        <div class="flex items-center justify-between">
            <div>
                <div class="section-label" style="color: var(--gold);">Editing a past day</div>
                <div class="font-display text-lg font-bold">{{ \Carbon\Carbon::parse($log_date)->format('l, M j') }}</div>
            </div>
            <button type="button" wire:click="closeForm" class="btn-ghost text-xs px-3 py-1.5">← Back to today</button>
        </div>
    </div>
    @endif

    {{-- Open form (today) --}}
    @if($isToday && !$showForm)
    <button type="button" wire:click="openForm" class="btn-primary w-full py-3">＋ Log / edit today</button>
    @endif

    {{-- ═══════════ FORM (Livewire-driven; auto-saves) ═══════════ --}}
    @if($showForm)
    <div id="logFormSection" class="space-y-4">

        <div class="flex items-center justify-between">
            <div class="section-label">{{ $isToday ? 'Logging today' : 'Editing ' . \Carbon\Carbon::parse($log_date)->format('M j') }}</div>
            <div class="flex items-center gap-3">
                <span class="text-xs" wire:loading.remove style="color: #2ecc71;">✓ Saved automatically</span>
                <span class="text-xs" wire:loading style="color: var(--text-muted);">Saving…</span>
                <button type="button" wire:click="closeForm" class="btn-ghost text-xs px-3 py-1.5">Done</button>
            </div>
        </div>

        {{-- Weigh-in (today only — weigh-ins are real-time) + water --}}
        <div class="grid gap-4 lg:grid-cols-2 items-start">
            @if($isToday)
            @include('livewire.partials.weigh-in')
            @endif

            <div class="card">
                <div class="flex items-center justify-between mb-3">
                    <div class="section-label" style="color: #3498db;">💧 Water</div>
                    <div class="font-display text-2xl font-bold" style="color: #3498db;">{{ number_format($water_liters, 1) }} L</div>
                </div>
                <div class="grid grid-cols-4 gap-2">
                    <button type="button" wire:click="addWater(0.25)" class="water-btn">+250ml</button>
                    <button type="button" wire:click="addWater(0.5)"  class="water-btn">+500ml</button>
                    <button type="button" wire:click="addWater(0.75)" class="water-btn">+750ml</button>
                    <button type="button" wire:click="addWater(1)"    class="water-btn">+1L</button>
                </div>
                @if($water_liters > 0)
                <button type="button" wire:click="addWater(-0.25)" class="btn-ghost text-xs px-3 py-1.5 mt-2">↺ Undo 250ml</button>
                @endif
            </div>
        </div>

        {{-- Training + wellbeing --}}
        <div class="grid gap-4 lg:grid-cols-2 items-start">
            <div class="card">
                <div class="flex items-center justify-between mb-3">
                    <div class="section-label">🥊 Training sessions</div>
                    @if($training_minutes)
                    <span class="text-xs" style="color: var(--text-muted);">{{ $training_minutes }}m · {{ count($sessions) }} session{{ count($sessions) == 1 ? '' : 's' }}</span>
                    @endif
                </div>
                @forelse($sessions as $i => $s)
                <div wire:key="session-{{ $i }}" class="flex items-center gap-2 mb-2">
                    <select wire:model.live="sessions.{{ $i }}.type" class="input-dark" style="flex: 2;">
                        @foreach($sessionTypes as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <input type="number" wire:model.blur="sessions.{{ $i }}.minutes" class="input-dark" placeholder="min" min="0" max="600" style="flex: 1; min-width: 60px;">
                    <select wire:model.live="sessions.{{ $i }}.when" class="input-dark" style="flex: 1.3;">
                        <option value="morning">🌅 AM</option>
                        <option value="afternoon">☀️ Noon</option>
                        <option value="evening">🌙 PM</option>
                    </select>
                    <button type="button" wire:click="removeSession({{ $i }})" title="Remove session"
                            class="flex items-center justify-center rounded-lg flex-shrink-0"
                            style="width: 34px; height: 34px; color: var(--text-muted); background: rgba(255,255,255,0.04);">✕</button>
                </div>
                @empty
                <p class="text-sm mb-2" style="color: var(--text-muted);">Rest day — or add a session.</p>
                @endforelse
                <button type="button" wire:click="addSession" class="btn-ghost text-xs px-3 py-2 w-full">+ Add session</button>
                <div class="mt-3">
                    <label class="text-xs mb-1 block" style="color: var(--text-muted);">Sleep last night (hours)</label>
                    <input type="number" step="0.5" wire:model.blur="sleep_hours" class="input-dark" placeholder="8" min="0" max="24">
                </div>
            </div>

            <div class="card">
                <div class="section-label mb-3">🧠 How you feel</div>
                <div class="flex justify-around py-1">
                    @foreach(['great' => ['🔥','Great'], 'good' => ['💪','Good'], 'okay' => ['😐','Okay'], 'tired' => ['😴','Tired'], 'bad' => ['🤕','Rough']] as $m => [$emoji, $label])
                    <button type="button" wire:click="$set('mood', '{{ $m }}')"
                            class="mood-btn flex flex-col items-center gap-1 {{ $mood === $m ? 'selected' : '' }}">
                        <span>{{ $emoji }}</span>
                        <span class="text-xs" style="color: var(--text-muted); font-size: 0.6rem;">{{ $label }}</span>
                    </button>
                    @endforeach
                </div>
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs" style="color: var(--text-muted);">Energy</span>
                        <span class="font-display text-lg font-bold" style="color: var(--blood);">{{ $energy_level }}/10</span>
                    </div>
                    <input type="range" min="1" max="10" wire:model.live.debounce.400ms="energy_level"
                           class="w-full" style="accent-color: var(--blood);">
                </div>
            </div>
        </div>

        {{-- Sugar & alcohol --}}
        <div class="card">
            <div class="flex items-center justify-between mb-3">
                <div class="section-label">🚫 Sugar & alcohol</div>
                @if($emptyKcal > 0)
                <span class="text-xs font-semibold" style="color: var(--blood);">≈ {{ number_format($emptyKcal) }} empty kcal</span>
                @endif
            </div>

            <label class="text-xs mb-1.5 block" style="color: var(--text-muted);">🥤 Soda / sugary drinks</label>
            <div class="flex gap-1.5 flex-wrap">
                @foreach([0=>'None',1=>'1',2=>'2',3=>'3',4=>'4',5=>'5+'] as $v => $lbl)
                <button type="button" wire:click="$set('soda_cans', {{ $v }})"
                        class="text-xs px-3 py-1.5 rounded-lg"
                        style="border: 1px solid {{ $soda_cans === $v ? 'var(--gold)' : 'var(--dark-border)' }};
                               background: {{ $soda_cans === $v ? 'rgba(243,156,18,0.15)' : 'transparent' }};
                               color: {{ $soda_cans === $v ? 'var(--gold)' : 'var(--text-muted)' }};">{{ $lbl }}</button>
                @endforeach
            </div>
            @if($soda_cans > 0)
            <div class="text-xs mt-1.5" style="color: var(--text-muted);">≈ {{ $sodaSugar }}g sugar · {{ number_format($sodaKcal) }} kcal</div>
            @endif

            <label class="text-xs mb-1.5 mt-4 block" style="color: var(--text-muted);">🍺 Alcohol — tap what you drank</label>
            <div class="space-y-1">
                @foreach($drinkTypes as $key => $d)
                <div class="flex items-center justify-between py-1">
                    <div class="text-sm">
                        {{ $d[0] }} {{ $d[1] }}
                        <span class="text-xs" style="color: var(--text-muted);">{{ $d[2] }}cl · {{ $d[3] }}kcal</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="adjustDrink('{{ $key }}', -1)" class="btn-ghost px-3 py-1 text-sm font-bold">−</button>
                        <span class="w-5 text-center font-display font-bold">{{ $alcohol_drinks[$key] ?? 0 }}</span>
                        <button type="button" wire:click="adjustDrink('{{ $key }}', 1)" class="btn-ghost px-3 py-1 text-sm font-bold">+</button>
                    </div>
                </div>
                @endforeach
            </div>
            @if($totalDrinks > 0)
            <div class="text-xs mt-2" style="color: var(--text-muted);">{{ $totalDrinks }} drinks · ≈ {{ number_format($alcoholKcal) }} kcal · dehydrating</div>
            @endif

            @if(($soda_cans > 0 || $totalDrinks > 0) && $inCamp)
            <div class="mt-3 pt-3 text-xs" style="border-top: 1px solid var(--dark-border); color: #ff6b6b;">
                ⚠ {{ $fightDays }} days to your fight — sugar & alcohol stall your cut and slow recovery.
            </div>
            @endif
        </div>

        {{-- Notes --}}
        <div class="card">
            <div class="section-label mb-2">📝 Notes</div>
            <textarea wire:model.blur="notes" rows="2" class="input-dark" placeholder="How did training go? Anything to remember?"></textarea>
        </div>

        <button type="button" wire:click="closeForm" class="btn-primary w-full py-3">Done</button>
        <p class="text-xs text-center" style="color: var(--text-muted);">Everything you tap or type here saves automatically.</p>
    </div>
    @endif

    {{-- History --}}
    @if($logs->count() > 0)
    <div class="card">
        <div class="flex items-baseline justify-between mb-3">
            <div class="section-label">Recent days</div>
            <span class="text-xs" style="color: var(--text-muted);">tap Edit to fix any day</span>
        </div>
        <div class="space-y-2">
            @foreach($logs as $log)
            @php
                $dKey      = $log->log_date->format('Y-m-d');
                $dayName   = $log->log_date->isToday() ? 'Today' : ($log->log_date->isYesterday() ? 'Yesterday' : $log->log_date->format('M j'));
                $sessCount = is_array($log->sessions) ? count($log->sessions) : 0;
            @endphp
            <div wire:key="log-{{ $dKey }}" class="rounded-xl p-3 transition hover:bg-white/5"
                 style="background: rgba(255,255,255,0.02); border-left: 3px solid {{ $log->log_date->isToday() ? 'var(--gold)' : 'transparent' }};">

                {{-- Date + mood + actions --}}
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="text-lg flex-shrink-0">{{ $moodMap[$log->mood ?? 'good'] ?? '💪' }}</span>
                        <span class="text-sm font-bold flex-shrink-0" style="{{ $log->log_date->isToday() ? 'color: var(--gold);' : '' }}">{{ $dayName }}</span>
                        <span class="text-xs truncate" style="color: var(--text-muted);">{{ $log->log_date->format('D, M j') }}</span>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <button type="button" wire:click="editLog('{{ $dKey }}')"
                                class="text-xs px-3 py-1 rounded-lg"
                                style="border: 1px solid rgba(243,156,18,0.3); color: var(--gold); background: rgba(243,156,18,0.08);">Edit</button>
                        <button type="button" wire:click="deleteLog('{{ $dKey }}')" wire:confirm="Delete this day's log?" title="Delete day"
                                class="flex items-center justify-center rounded-lg"
                                style="width: 28px; height: 28px; color: var(--text-muted); background: rgba(255,255,255,0.04);">✕</button>
                    </div>
                </div>

                {{-- Stats — fixed grid so layout never shifts --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div>
                        <div class="font-display text-base font-bold leading-none">{{ isset($weightByDate[$dKey]) ? number_format($weightByDate[$dKey], 1) : '—' }}<span class="text-xs font-normal" style="color: var(--text-muted);"> kg</span></div>
                        <div class="text-xs mt-1" style="color: var(--text-muted);">⚖️ Weight</div>
                    </div>
                    <div>
                        <div class="font-display text-base font-bold leading-none" style="color: #3498db;">{{ number_format($log->water_liters, 1) }}<span class="text-xs font-normal" style="color: var(--text-muted);"> L</span></div>
                        <div class="text-xs mt-1" style="color: var(--text-muted);">💧 Water</div>
                    </div>
                    <div>
                        <div class="font-display text-base font-bold leading-none" style="color: {{ $log->training_minutes ? 'var(--gold)' : 'var(--text-muted)' }};">{{ $log->training_minutes ? $log->training_minutes.'m' : 'Rest' }}</div>
                        <div class="text-xs mt-1 truncate" style="color: var(--text-muted);">{{ $log->training_minutes ? ($trainIcons[$log->training_type] ?? '🏋️').' '.($sessCount > 1 ? $sessCount.' sessions' : $log->training_type) : '💤 rest day' }}</div>
                    </div>
                    <div>
                        <div class="font-display text-base font-bold leading-none">{{ $log->sleep_hours ?? '—' }}<span class="text-xs font-normal" style="color: var(--text-muted);"> h</span></div>
                        <div class="text-xs mt-1" style="color: var(--text-muted);">😴 Sleep</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
